<?php
/**
 * Created by PhpStorm.
 * User: wangzhan
 * Date: 2017/11/21
 * Time: 下午7:02
 */
namespace Common\Library;
use Phalcon\Di;
use Phalcon\Exception;

/**
 * kafka公共调用类
 * Class Kafka
 * @package Librarys
 */
class Kafka
{
    protected $topicTpl = 'resume_lib_{member}';//topic模板
    protected $groupTpl = 'resume_lib_group_{member}';//group模板
    protected $topic = '';//定义topic
    protected $group = '';//定义组
    protected $isTopic = false;//是否设置了topic
    protected $isGroup = false;//是否设置了group

    protected $brokerList;//kafka服务器地址端口

    private static $producer = [];//生产者
    private static $consumer = [];//消费者

    protected $di = null;

    /**
     * 根据客户设置不同的topic
     * Kafka constructor.
     * @param null $member
     */
    public function __construct($member = null)
    {
        $this->generalTopic($member);
        $this->generalGroup($member);

        $this->di = Di::getDefault();

        $kafkaConf = $this->di->getShared('config')->kafka;
        $this->brokerList = $kafkaConf->host;
    }

    /**
     * 设置topic和group
     * @param null $member
     */
    public function setTopic($member = null)
    {
        $this->generalTopic($member);
        $this->generalGroup($member);
    }

    /**
     * 设置topic
     * @param null $member
     */
    private function generalTopic($member = null)
    {
        //必须传客户标识
        if (!empty($member)) {
            $this->topic = str_replace('{member}', $member, $this->topicTpl);
            $this->isTopic = true;
        }
    }

    /**
     * 设置组
     * @param null $member
     */
    private function generalGroup($member = null)
    {
        //必须传客户标识
        if (!empty($member)) {
            $this->group = str_replace('{member}', $member, $this->groupTpl);
            $this->isGroup = true;
        }
    }

    /**
     * 关键参数或者变量是否已经正确填值
     * @return bool
     */
    protected function paramsOk()
    {
        if (!$this->isTopic || !$this->isGroup) {
            return false;
        }

        return true;
    }


    /**
     * 初始化producer
     * @return bool|null|\RdKafka\Producer
     * @throws \Exception
     */
    private function initProducer()
    {
        if(!isset(self::$producer[$this->topic])){
            try {
                if(empty($this->brokerList)){
                    throw new \Exception("broker_list is null", Constants::ERR_KAFKA_EMPTY_BROKER);
                }

                $conf = new \RdKafka\Conf();
                $conf->set('socket.timeout.ms', 50); // or socket.blocking.max.ms, depending on librdkafka version
                $conf->set('compression.codec', 'gzip');
                if (function_exists('pcntl_sigprocmask')) {
                    pcntl_sigprocmask(SIG_BLOCK, array(SIGIO));
                    $conf->set('internal.termination.signal', SIGIO);
                } else {
                    //发送消息前等待的毫秒数，与batch.size配合使用。在消息负载不高的情况下，配置linger.ms能够让Producer在发送消息前等待一定时间，以积累更多的消息打包发送，达到节省网络资源的目
                    $conf->set('queue.buffering.max.ms', 1);
                }

                $rk = new \RdKafka\Producer($conf);  //创建生产者
                if(!isset($rk)){
                    throw new \Exception("create producer error", Constants::ERR_KAFKA_INIT_PRODUCER);
                }

                $rk->setLogLevel(LOG_DEBUG);
                if(!$rk->addBrokers($this->brokerList)){  //设置kafka服务器
                    throw new \Exception("add producer error", Constants::ERR_KAFKA_ADD_BROKER);
                }
                self::$producer[$this->topic] = $rk;

            } catch (Exception $e) {
                return false;
            }
            return self::$producer[$this->topic];
        } else {
            return self::$producer[$this->topic];
        }
    }

    /**
     * producer发送数据到kafka队列
     * @param string $data 待入队列的数据
     * @param string $topic 主题
     * @param int $partition 主题下分区
     * @TODO 后续可做成多进程接收数据
     */
    public function kPush($data = '')
    {
        if (!$this->paramsOk()) {
            return false;
        }

        $producer = $this->initProducer();
        if (!$producer) {
            return false;
        }

        try {
            $topicConf = new \RdKafka\TopicConf();
            //集群完整确认，Leader会等待所有in-sync的follower节点都确认收到消息后，再返回确认信息,避免消息丢失
            $topicConf->set('request.required.acks', -1);

            $topic = $producer->newTopic($this->topic);//创建主题topic
            //向指定的topic物理地址发消息

            if (!is_scalar($data)) {
                $data = json_encode($data);
            }

            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $data);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 初始化consumer
     * @return bool|null|\RdKafka\KafkaConsumer
     */
    private function initConsumer()
    {
        if (!$this->paramsOk()) {
            return false;
        }

        try {
            $conf = new \RdKafka\Conf();

            // 当有新的消费进程加入或者退出消费组时，kafka 会自动重新分配分区给消费者进程，这里注册了一个回调函数，当分区被重新分配时触发
            $conf->setRebalanceCb(function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
                switch ($err) {
                    case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                        echo "Assign: ";
                        var_dump($partitions);
                        $kafka->assign($partitions);
                        break;

                    case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                        echo "Revoke: ";
                        var_dump($partitions);
                        $kafka->assign(NULL);
                        break;
                    default:
                        throw new \Exception($err);
                }
            });

            //配置groud.id 具有相同 group.id 的consumer 将会处理不同分区的消息，所以同一个组内的消费者数量如果订阅了一个topic， 那么消费者进程的数量多于 多于这个topic 分区的数量是没有意义的。
            $conf->set('group.id', $this->group);

            pcntl_sigprocmask(SIG_BLOCK, array(SIGIO));
            //any time
            $conf->set('internal.termination.signal', SIGIO);
            //添加 kafka集群服务器地址
            $conf->set('metadata.broker.list', $this->brokerList);
            //关闭自动提交
            $conf->set('enable.auto.commit', 'false');

            $topicConf = new \RdKafka\TopicConf();


            // Set where to start consuming messages when there is no initial offset in
            // offset store or the desired offset is out of range.
            // 'smallest': start from the beginning
            //当没有初始偏移量时，从哪里开始读取
            $topicConf->set('auto.offset.reset', 'smallest');


            //Set the configuration to use for subscribed/assigned topics
            $conf->setDefaultTopicConf($topicConf);

            $consumer = new \RdKafka\KafkaConsumer($conf);

            //让消费者订阅log 主题
            $consumer->subscribe([$this->topic]);
        } catch (Exception $e) {
            return false;
        }

        return $consumer;
    }

    protected function consumerInstance()
    {
        if (!isset(self::$consumer[$this->topic])) {
            self::$consumer[$this->topic] = $this->initConsumer();
        }
        return self::$consumer[$this->topic];
    }

    /**
     * 从kafka队列获取数据
     * @param string $topic 主题
     * @param int $partition 主题下分区
     */
    public function kPop()
    {
        if (!$this->paramsOk()) {
            return false;
        }

        $consumer = $this->consumerInstance();

        if (!$consumer) {
            return false;
        }

        try {
            $messageObj = $consumer->consume(120*1000);
            $code = $messageObj->err;
            switch ($code) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $message = $messageObj->payload;//获取消息
                    $consumer->commitAsync($messageObj);//获取到消息后异步提交
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    $message = false;
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    $message = false;
                    break;
                default:
                    $message = false;
                    break;
            }

            return $message;
        } catch (Exceptiop $e) {
            return false;
        }
    }
}