<?php

use org\bovigo\vfs\vfsStream;

class SecretsTest extends PHPUnit_Framework_TestCase
{

	protected $environment = null;

	public function setUp()
	{
		$this->environment = \Config\Environment::name();
	}

	public function tearDown()
	{
		\Config\Environment::define($this->environment);
	}

	public function testSecretsAreUsedFromEnvironment()
	{
		$environmentConfig = array(
			'secrets' => array(
				'api' => array(
					'hmac_keys' => array(
						'api_proxy' => 'key from environment',
					),
				),
			),
		);
		Config\Environment::override(new Config\Properties($environmentConfig));

		$secrets = new Config\Secrets();

		$this->assertEquals('key from environment', $secrets->get('api.hmac_keys.api_proxy'));
		Config\Environment::restore();
	}

	public function testSecretsAreLoadedFromLoadPath()
	{
		$secretJson = '{"api": {"hmac_keys": {"api_proxy":"key from vfs"}}}';
		$structure = array(
			'opt' => array(
				'bigcommerce_app' => array(
					'secrets.json' => $secretJson,
				),
			),
		);
		vfsStream::setup('/', 0755, $structure);

		$environmentConfig = array(
			'secrets' => array(
				'load_path' => vfsStream::url('/opt/bigcommerce_app/secrets.json'),
			),
		);
		Config\Environment::override(new Config\Properties($environmentConfig));

		$secrets = new Config\Secrets();

		$this->assertEquals('key from vfs', $secrets->get('api.hmac_keys.api_proxy'));
		Config\Environment::restore();
	}

	public function testLoadPathSecretsAreMergedWithEnvironment()
	{
		$environmentConfig = array(
			'secrets' => array(
				'load_path' => vfsStream::url('/opt/bigcommerce_app/secrets.json'),
				'foo' => 'bar',
			),
		);

		$fileSecrets = array(
			'bar' => 'baz',
		);

		vfsStream::setup('/', 0755, array(
			'opt' => array(
				'bigcommerce_app' => array(
					'secrets.json' => json_encode($fileSecrets),
				),
			),
		));

		Config\Environment::override(new Config\Properties($environmentConfig));

		$secrets = new Config\Secrets();

		$this->assertSame("bar", $secrets->get("foo"));
		$this->assertSame("baz", $secrets->get("bar"));

		Config\Environment::restore();
	}

	public function testMissingFileInDevelopment()
	{

		\Config\Environment::define(\Config\Environment::DEVELOPMENT);

		Config\Environment::override(new Config\Properties(array(
			'secrets' => array(
				'foo' => 'bar',
				'bar' => 'baz',
			),
		)));

		$secrets = new Config\Secrets();

		$this->assertSame("bar", $secrets->get("foo"));
		$this->assertSame("baz", $secrets->get("bar"));

		Config\Environment::restore();
	}

	public function testMissingFileInStaging()
	{

		\Config\Environment::define(\Config\Environment::STAGING);

		Config\Environment::override(new Config\Properties(array(
			'bar' => 'foo',
			'baz' => 'bar',
		)));

		$this->setExpectedException('RuntimeException');
		new Config\Secrets();

		Config\Environment::restore();
	}

	public function testMissingFileInProduction()
	{

		\Config\Environment::define(\Config\Environment::PRODUCTION);

		Config\Environment::override(new Config\Properties(array(
			'bar' => 'foo',
			'baz' => 'bar',
		)));

		$this->setExpectedException('RuntimeException');
		new Config\Secrets();

		Config\Environment::restore();
	}

	public function testSecretsAreUsedFromPropertiesInstance()
	{
		$environmentConfig = array(
			'api' => array(
				'hmac_keys' => array(
					'api_proxy' => 'key from Properties',
				),
			),
		);

		$secrets = new Config\Secrets(new Config\Properties($environmentConfig));

		$this->assertEquals('key from Properties', $secrets->get('api.hmac_keys.api_proxy'));
	}

	public function testRuntimeExceptionThrownWithMissingSecretsFile()
	{
		$environmentConfig = array(
			'load_path' => 'missing_file.json',
		);
		$properties = new Config\Properties($environmentConfig);

		try {
			$secrets = new Config\Secrets($properties);
		}
		catch(RuntimeException $e) {
			$this->assertEquals('Shared secrets file - missing_file.json - does not exist. Environment: test', $e->getMessage());
		}
	}

	public function testRuntimeExceptionThrownWithInvalidJson()
	{
		$secretJson = '!)(@&%invalid json string@&$)(@*#%';
		$structure = array(
			'opt' => array(
				'bigcommerce_app' => array(
					'secrets.json' => $secretJson,
				),
			),
		);
		vfsStream::setup('/', 0755, $structure);
		$environmentConfig = array(
			'load_path' => vfsStream::url('/opt/bigcommerce_app/secrets.json'),
		);

		try {
			$secrets = new Config\Secrets(new Config\Properties($environmentConfig));
		}
		catch(RuntimeException $e) {
			$this->assertEquals('Error parsing  - vfs:///opt/bigcommerce_app/secrets.json. Environment: test', $e->getMessage());
		}
	}
}
