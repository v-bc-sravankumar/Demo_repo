<?php

class Unit_Lib_Interspire_SslCertificate extends Interspire_IntegrationTest
{
	private $_validCertificate = "-----BEGIN CERTIFICATE-----
MIIDhDCCAmwCCQDvITr1pQvrDTANBgkqhkiG9w0BAQUFADCBgzELMAkGA1UEBhMC
QVUxDDAKBgNVBAgTA05TVzEPMA0GA1UEBxMGU3lkbmV5MRQwEgYDVQQKEwtCaWdD
b21tZXJjZTEaMBgGA1UEAxQRKi5iaWdjb21tZXJjZS5jb20xIzAhBgkqhkiG9w0B
CQEWFHRlc3RAYmlnY29tbWVyY2UuY29tMB4XDTExMDgyNTA2MDk1NloXDTE3MDIx
NDA2MDk1NlowgYMxCzAJBgNVBAYTAkFVMQwwCgYDVQQIEwNOU1cxDzANBgNVBAcT
BlN5ZG5leTEUMBIGA1UEChMLQmlnQ29tbWVyY2UxGjAYBgNVBAMUESouYmlnY29t
bWVyY2UuY29tMSMwIQYJKoZIhvcNAQkBFhR0ZXN0QGJpZ2NvbW1lcmNlLmNvbTCC
ASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAJ8uA0Ku2hMyy77hXoRoOfOR
WikwYf5H+/nxyQC++45Z5OX0aNWy+0K813HyzoO6Pvy6XodOrtsFY2yTHIezrNuv
De8jLfV+R0CTpvmbWhBUhUh9oiisHo5Xds6ktPJpWEUdzAhH0rSVcobJs0pobXit
lG+m/yFM+mB9bC1PMRb3BJehj6SZT1aonWfsQZDPkGjr8DIJx/DqS3F5Hv0ioJnG
i8t+H4kXkQJZHLITj/n/xx13de8nmsBBte4A6zlngx/MAvpWIQuIetFeYxHRS3ZO
H/rhZj4csUG7ronwZNPZbeYFTvXaR+aaS57EtiDqfFIueEOEOWPLhdMW+DSChF0C
AwEAATANBgkqhkiG9w0BAQUFAAOCAQEARL1Lut+o2mmAJ9wxquuuq2KJl5qgNqnm
os4NVYtMcUmHcUG7GL2js5bVFV4WIm3zIOquE8SuNybiswBj9ltA7Gj8JyUJUdXL
u26C2/e0DukWRCeslGmNLHAnJNsE1zTNlL/DURuM9muciIujrTO+IYRWi41dQ4+X
VhOnHLAOqFbQuDKNvHHRHhjnYeY6bOIk0Iq/DCaGJTbZbpVSEvLIumTHoKI9NTZx
Ly0wokf6Oywpe12Gc48X/MCOmMPflmpkv4CwN7cVgsbNcM0foSnrBZ2BJZSUUJib
3MQekkyoC7WDXUIgutYuVqOaLJdWOG+1ZHWvIi70Yk2ypMNZs/0RUg==
-----END CERTIFICATE-----";

	private $_expiredCertificate = "-----BEGIN CERTIFICATE-----
MIIDhDCCAmwCCQCoc3SKkPnq3zANBgkqhkiG9w0BAQUFADCBgzELMAkGA1UEBhMC
QVUxDDAKBgNVBAgTA05TVzEPMA0GA1UEBxMGU3lkbmV5MRQwEgYDVQQKEwtCaWdD
b21tZXJjZTEaMBgGA1UEAxQRKi5iaWdjb21tZXJjZS5jb20xIzAhBgkqhkiG9w0B
CQEWFHRlc3RAYmlnY29tbWVyY2UuY29tMB4XDTExMDgyNTA2MTAxMloXDTExMDgy
NDA2MTAxMlowgYMxCzAJBgNVBAYTAkFVMQwwCgYDVQQIEwNOU1cxDzANBgNVBAcT
BlN5ZG5leTEUMBIGA1UEChMLQmlnQ29tbWVyY2UxGjAYBgNVBAMUESouYmlnY29t
bWVyY2UuY29tMSMwIQYJKoZIhvcNAQkBFhR0ZXN0QGJpZ2NvbW1lcmNlLmNvbTCC
ASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAJ8uA0Ku2hMyy77hXoRoOfOR
WikwYf5H+/nxyQC++45Z5OX0aNWy+0K813HyzoO6Pvy6XodOrtsFY2yTHIezrNuv
De8jLfV+R0CTpvmbWhBUhUh9oiisHo5Xds6ktPJpWEUdzAhH0rSVcobJs0pobXit
lG+m/yFM+mB9bC1PMRb3BJehj6SZT1aonWfsQZDPkGjr8DIJx/DqS3F5Hv0ioJnG
i8t+H4kXkQJZHLITj/n/xx13de8nmsBBte4A6zlngx/MAvpWIQuIetFeYxHRS3ZO
H/rhZj4csUG7ronwZNPZbeYFTvXaR+aaS57EtiDqfFIueEOEOWPLhdMW+DSChF0C
AwEAATANBgkqhkiG9w0BAQUFAAOCAQEAmqjfYENnkhRRKafFa9pPvl/EOk9OWx7Y
GS+vIc8jtJRwwXuEuZYXhW4Hn3nZ+zZWyKAEQjQVipMVWWgOpOKTaGZc8E2KyFkD
ufXV6HQHBalVQpizNpy9Y6Cd+0BHtoGrI2YPPu7jg4le/z/ynf+soNZei/uxGVgi
nyF/lwL+R4vk4sDmrvSVqdtsuWulaUZCP+VH6evNLuoVAS3PF8E4ktcAnNgIe7Zg
kzbkNioMJaU6KO2AotoaMvtTD1gp7hnl2OreYL4E9GN2izi7/+7UMLEYb/RRPEHW
xRq41J8DRpNgCncB7GdTYPpU5TNzorz5/yPMJ4OlYaEx8Re5enNYVg==
-----END CERTIFICATE-----";

	private $_key = "-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAny4DQq7aEzLLvuFehGg585FaKTBh/kf7+fHJAL77jlnk5fRo
1bL7QrzXcfLOg7o+/Lpeh06u2wVjbJMch7Os268N7yMt9X5HQJOm+ZtaEFSFSH2i
KKwejld2zqS08mlYRR3MCEfStJVyhsmzSmhteK2Ub6b/IUz6YH1sLU8xFvcEl6GP
pJlPVqidZ+xBkM+QaOvwMgnH8OpLcXke/SKgmcaLy34fiReRAlkcshOP+f/HHXd1
7yeawEG17gDrOWeDH8wC+lYhC4h60V5jEdFLdk4f+uFmPhyxQbuuifBk09lt5gVO
9dpH5ppLnsS2IOp8Ui54Q4Q5Y8uF0xb4NIKEXQIDAQABAoIBABUR/yZ9hKpT5/pd
VKiML1eZXuji2aXSG32LTsMFhMDkD1ONFa9r4KyF2LLhpAp1xc6oUMjyVlzxiqad
loIz+2ac9mg4LOY5D+9xXAHgWXyd44Kj9qoRln3bAAP8c2M/JIsJla9m6nIy8hT7
b/sidZMqzuI0pcLfsKjDEWWx/NqAJKgVyg6Ktg87JPvB65kBKdwOBs/YO5GS2AcU
PSkQGJlTAOMO+VfzIr3aXXfUnJYSyrDZf13pbbXZdrjDIqaFFmztvlNM6F+gjwsY
cCUIcGkz6P05XtkH5702N1SKPZyLH15/FLeKXcq+oZtPiA3o8hhaWFx7ITz1x+VN
H0skrWECgYEA0IecPgutYY/edgaTB/lhK8/b4VSYeTzUxcPoLF5vXAcLtCTGJl8C
rnuNk0Rd1LCyeZ0dyTUiirROLcYEzfl1d1CJcxCQ8bH9AU3d4qUcJdecY1brsSIP
1a7rDuiZ40jjDZwlkXOxENSAesYWVBxcW4NoFVui1dEHkjUO6eezpCkCgYEAw2p0
vqCZ337O/LYp0j/aMoNJotyk/zWAzZiqSjJrkX5ZoUMn3RpAAhpIiDZ+gYPPLv+f
KiHmvMpf72A0rEQOHKE/sTMB5Zaaap8M2DQH8Gx895kAPY1n36R2mg9dOtfQhGuP
rFCzGx8Som3DFpO1Z+sP54d8aepSQObcoQQvRRUCgYA1kD/f6Bv3DX91DadCyxnc
qR2vuY0YxzlYO0Qt8WvlVaH5+eA0Bv+nVfE9vLflZCXT+zmlb1KaEkpqk4y0Y7l6
lmNX/Q9eJfv5E8lE6GhciA+RrMgJzdgHaVDTmYe9zAEWg99ahz8aNZty7eLaZBaN
IynfIpSnG3Q4aAyWpH+OUQKBgA4sHVVp7l7wInDfgT73VIrPTLrcB7demP3QMaF5
8KU3paZ1aWG2sqe0YkhGs3wPJCqDbXavyL0ubDC/KHLJ6MAYzba7PUr6Vi6fZF4V
v/Gm3JVUalkMdVkZ42Qe8yL+XegMqPnVTHgOE9rl3P05LzHfMWMYR2SjEt4UIDIp
TIhVAoGBAIts2QaihZF4jHb4AlZVr8LSC6wTTXywmaC7K9bngPeeEFCSEBdj+asT
h3eqbH5pb72em3E5kd47Mk8onrrpvwaKjjkqslpOj3e4l/FWWqHwjjHwTvv1HyJJ
GKNvc8jFn/XUx6i+rh4lSBb8GfvuLSf11qvazc+8N2GgYeejLSoe
-----END RSA PRIVATE KEY-----";

	/**
	* @expectedException Interspire_SslCertificate_Exception_NoCertificateSupplied
	*/
	public function testNoCertificateSuppliedFails()
	{
		$certificate = new Interspire_SslCertificate('');
	}

	/**
	* @expectedException Interspire_SslCertificate_Exception_InvalidCertificate
	*/
	public function testInvalidCertificateFails()
	{
		$certificate = new Interspire_SslCertificate('foo');
	}

	/**
	* @expectedException Interspire_SslCertificate_Exception_MultipleCertificatesSupplied
	*/
	public function testMultipleCertificatesFails()
	{
		$multiple = $this->_validCertificate . "\n" . $this->_validCertificate;
		$certificate = new Interspire_SslCertificate($multiple);
	}

	public function testGetRawCertificate()
	{
		$certificate = new Interspire_SslCertificate($this->_validCertificate);
		$this->assertEquals($this->_validCertificate, $certificate->getRawCertificate());
	}

	public function testGetCertificateData()
	{
		$certificate = new Interspire_SslCertificate($this->_validCertificate);
		$this->assertArrayIsNotEmpty($certificate->getCertificateData());
	}

	public function testValidCertificateNotExpired()
	{
		$certificate = new Interspire_SslCertificate($this->_validCertificate);
		$this->assertFalse($certificate->isExpired());
	}

	public function testExpiredCertificateIsExpired()
	{
		$certificate = new Interspire_SslCertificate($this->_expiredCertificate);
		$this->assertTrue($certificate->isExpired());
	}

	public function testInvalidKeyDoesntMatchCertificate()
	{
		$certificate = new Interspire_SslCertificate($this->_validCertificate);
		$this->assertFalse($certificate->isValidForPrivateKey('foo'));
	}

	public function testValidKeyMatchesCertificate()
	{
		$certificate = new Interspire_SslCertificate($this->_validCertificate);
		$this->assertTrue($certificate->isValidForPrivateKey($this->_key));
	}

	public function testDomainNotValidForCertificateFails()
	{
		$certificate = new Interspire_SslCertificate($this->_validCertificate);
		$this->assertFalse($certificate->isValidForDomains(array('foo.com')));
	}

	public function domainValidForCertificateDataProvider()
	{
		$domains = array(
			array('bigcommerce.com'),
			array('www.bigcommerce.com'),
		);

		return $domains;
	}

	/**
	* @dataProvider domainValidForCertificateDataProvider
	*/
	public function testDomainValidForCertificate($domain)
	{
		$certificate = new Interspire_SslCertificate($this->_validCertificate);
		$this->assertTrue($certificate->isValidForDomains(array($domain)), $domain . " should be valid.");
	}

	public function testMultipleSubdomainsNotValidForCertificate()
	{
		$certificate = new Interspire_SslCertificate($this->_validCertificate);
		$this->assertFalse($certificate->isValidForDomains(array('foo.bar.bigcommerce.com')));
	}
}
