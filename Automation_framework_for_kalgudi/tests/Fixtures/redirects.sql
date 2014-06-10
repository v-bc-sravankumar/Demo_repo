TRUNCATE `redirects`;
TRUNCATE `products`;
TRUNCATE `customers`;
TRUNCATE `brands`;
TRUNCATE `pages`;
TRUNCATE `news`;
TRUNCATE `categories`;

INSERT INTO `customers` (`customerid`) VALUES (1);
INSERT INTO `products` (`productid`) VALUES (1);
INSERT INTO `categories` (`categoryid`, `catdesc`) VALUES (1, 'category');
INSERT INTO `brands` (`brandid`, `brandname`) VALUES (1, 'test brand');
INSERT INTO `pages` (`pageid`) VALUES (1);
INSERT INTO `news` (`newsid`) VALUES (1);

INSERT INTO `redirects` (`redirectid`, `redirectpath`, `redirectassocid`, `redirectassoctype`, `redirectmanual`) VALUES (1, '/test_manual', 0, 0, '/example');
INSERT INTO `redirects` (`redirectid`, `redirectpath`, `redirectassocid`, `redirectassoctype`, `redirectmanual`) VALUES (2, '/test_brand', 1, 100, '');
INSERT INTO `redirects` (`redirectid`, `redirectpath`, `redirectassocid`, `redirectassoctype`, `redirectmanual`) VALUES (3, '/test_category', 1, 200, '');
INSERT INTO `redirects` (`redirectid`, `redirectpath`, `redirectassocid`, `redirectassoctype`, `redirectmanual`) VALUES (4, '/test_page', 1, 300, '');
INSERT INTO `redirects` (`redirectid`, `redirectpath`, `redirectassocid`, `redirectassoctype`, `redirectmanual`) VALUES (5, '/test_product', 1, 400, '');
INSERT INTO `redirects` (`redirectid`, `redirectpath`, `redirectassocid`, `redirectassoctype`, `redirectmanual`) VALUES (6, '/test_news', 1, 500, '');

