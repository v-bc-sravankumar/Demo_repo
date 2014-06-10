TRUNCATE module_vars;

INSERT INTO module_vars (modulename, variablename, variableval) VALUES ('checkout_cod', 'is_setup', '1');
INSERT INTO module_vars (modulename, variablename, variableval) VALUES ('checkout_cod', 'displayname', 'Cash on Delivery');
INSERT INTO module_vars (modulename, variablename, variableval) VALUES ('checkout_cod', 'helptext', 'You cash, we deliver');

INSERT INTO module_vars (modulename, variablename, variableval) VALUES ('checkout_instore', 'is_setup', '1');
INSERT INTO module_vars (modulename, variablename, variableval) VALUES ('checkout_instore', 'displayname', 'Pay in Store');
INSERT INTO module_vars (modulename, variablename, variableval) VALUES ('checkout_instore', 'helptext', 'Why you no pay?');

INSERT INTO module_vars (modulename, variablename, variableval) VALUES ('checkout_securenet', 'is_setup', '1');
INSERT INTO module_vars (modulename, variablename, variableval) VALUES ('checkout_securenet', 'displayname', 'SecureNet');
INSERT INTO module_vars (modulename, variablename, variableval) VALUES ('checkout_securenet', 'testmode', 'YES');
