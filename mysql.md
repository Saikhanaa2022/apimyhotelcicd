SET GLOBAL validate_password_policy=LOW;
UPDATE mysql.user SET authentication_string = PASSWORD('ar1unbold114') WHERE User = 'root';

INSERT INTO mysql.user (User,Host,authentication_string,ssl_cipher,x509_issuer,x509_subject)
VALUES('demouser','localhost',PASSWORD('ar1unbold114'),'','','');

CREATE DATABASE project CHARACTER SET utf8 COLLATE utf8_general_ci;

GRANT ALL PRIVILEGES ON link24.* to demouser@localhost;
