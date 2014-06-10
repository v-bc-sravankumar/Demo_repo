<?php

class Unit_Interspire_DownloadTest extends PHPUnit_Framework_TestCase
{
    public function testCanGenerateForceDownloadHeaders()
    {
        $headers = Interspire_Download::generateForceDownloadHeaders('foo.html');
        $this->assertInternalType('array', $headers);
    }

    public function testGenerateForceDownloadHeadersDeterminesContentType()
    {
        $headers = Interspire_Download::generateForceDownloadHeaders('foo.html');
        $this->assertSame('text/html; charset=UTF-8', $headers['Content-Type']);
    }

    public function testGenerateForceDownloadHeadersDeterminesContentDisposition()
    {
        $headers = Interspire_Download::generateForceDownloadHeaders('foo.html');
        $this->assertSame('attachment; filename="foo.html"', $headers['Content-Disposition']);
    }

    public function testGenerateForceDownloadHeadersSendsPublicHeadersUnderSsl()
    {
        // a Chrome OSX UA
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.19 Safari/537.36';

        $server = array(
            'HTTPS' => 'on',
            'HTTP_USER_AGENT' => $userAgent,
        );

        $headers = Interspire_Download::generateForceDownloadHeaders('foo.html', null, null, $server);

        $this->assertSame('must-revalidate, post-check=0, pre-check=0', $headers['Cache-Control']);
        $this->assertSame('public', $headers['Pragma']);
    }

    public function testGenerateForceDownloadHeadersSendsPublicHeadersToIe()
    {
        // an IE UA
        $userAgent = 'Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0';

        $server = array(
            'HTTP_USER_AGENT' => $userAgent,
        );

        $headers = Interspire_Download::generateForceDownloadHeaders('foo.html', null, null, $server);

        $this->assertSame('must-revalidate, post-check=0, pre-check=0', $headers['Cache-Control']);
        $this->assertSame('public', $headers['Pragma']);
    }

    public function testGenerateForceDownloadHeadersSendsPrivateHeadersToIeUnderSsl()
    {
        // an IE UA
        $userAgent = 'Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0';

        $server = array(
            'HTTPS' => 'on',
            'HTTP_USER_AGENT' => $userAgent,
        );

        $headers = Interspire_Download::generateForceDownloadHeaders('foo.html', null, null, $server);

        $this->assertSame('private', $headers['Cache-Control']);
        $this->assertSame('private', $headers['Pragma']);
    }
}
