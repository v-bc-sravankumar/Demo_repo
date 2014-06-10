<?php

require_once "PubSubRabbitMQ.php";

putenv("RABBITMQ_URI=".TEST_RABBITMQ_URI_SSL);
putenv("RABBITMQ_SSL_ENABLED=1");
putenv("RABBITMQ_SSL_CAPATH=".TEST_RABBITMQ_SSL_CAPATH);

class MessageQueue_PubSubRabbitMQSSL extends MessageQueue_PubSubRabbitMQ {
	// does the same tests but with SSL connection
}