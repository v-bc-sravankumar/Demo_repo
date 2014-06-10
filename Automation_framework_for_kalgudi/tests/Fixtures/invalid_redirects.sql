-- Invalid redirects
TRUNCATE `redirects`;
INSERT INTO `redirects` (`redirectid`, `redirectpath`, `redirectassocid`, `redirectassoctype`, `redirectmanual`) VALUES (2, '/invalid_test_brand', 2, 100, '');
INSERT INTO `redirects` (`redirectid`, `redirectpath`, `redirectassocid`, `redirectassoctype`, `redirectmanual`) VALUES (3, '/invalid_test_category', 2, 200, '');
INSERT INTO `redirects` (`redirectid`, `redirectpath`, `redirectassocid`, `redirectassoctype`, `redirectmanual`) VALUES (4, '/invalid_test_page', 2, 300, '');
INSERT INTO `redirects` (`redirectid`, `redirectpath`, `redirectassocid`, `redirectassoctype`, `redirectmanual`) VALUES (5, '/invalid_test_product', 2, 400, '');
INSERT INTO `redirects` (`redirectid`, `redirectpath`, `redirectassocid`, `redirectassoctype`, `redirectmanual`) VALUES (6, '/invalid_test_news', 2, 500, '');