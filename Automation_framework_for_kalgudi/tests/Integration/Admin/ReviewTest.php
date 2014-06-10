<?php

namespace Integration\Admin;

class ReviewTest extends \PHPUnit_Framework_TestCase
{
	public function testGetMessages()
	{
		$file = ISC_BASE_PATH.'/language/en/admin/reviews.ini';
		ParseLangFile($file, true);

		$admin = new ISC_ADMIN_REVIEW_TEST();

		//NotUsingBuiltInWarning
		\Store_Config::override('CommentSystemModule', 'comments_disqus');
		$messages = $admin->GetMessages(1);
		$this->assertTrue((bool)preg_match("/.Disqus./i", $messages));

		//BuiltInCustOnlyProdReviewAvailable
		\Store_Config::override('CommentSystemModule', 'comments_builtincomments');
		\Store_Config::override('CommentSystemBuiltInCustOnlyProdReviews', 0);
		$messages = $admin->GetMessages(1);
		$this->assertTrue((bool)preg_match("/.To prevent spam./i", $messages));

		\Store_Config::override('CommentSystemBuiltInCustOnlyProdReviews', 1);
		$messages = $admin->GetMessages(1);
		$this->assertFalse((bool)preg_match("/.To prevent spam./i", $messages));

		//Desc
		$messages = $admin->GetMessages(1, "Desc test");
		$this->assertTrue((bool)preg_match("/.Desc./i", $messages));

		//numReviews
		$messages = $admin->GetMessages(0);
		$this->assertTrue((bool)preg_match("/.No product reviews./i", $messages));
	}
}


class ISC_ADMIN_REVIEW_TEST extends \ISC_ADMIN_REVIEW
{
	public function __construct()
	{
		$this->request = new \Interspire_Request();
	}
}