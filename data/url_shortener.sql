CREATE USER IF NOT EXISTS 'user'@'%' IDENTIFIED BY 'user';

CREATE DATABASE IF NOT EXISTS `url_shortener`;
CREATE DATABASE IF NOT EXISTS `url_shortener_test`;

GRANT ALL PRIVILEGES ON `url_shortener`.* TO 'user'@'%';
GRANT ALL PRIVILEGES ON `url_shortener_test`.* TO 'user'@'%';

USE `url_shortener`;

CREATE TABLE IF NOT EXISTS url (
    id INT(11) AUTO_INCREMENT,
    url VARCHAR(255) NOT NULL,
    hash VARCHAR(14) NOT NULL,
    created_date DATETIME NOT NULL,
    PRIMARY KEY (id)
    );

USE `url_shortener_test`;

CREATE TABLE IF NOT EXISTS url (
    id INT(11) AUTO_INCREMENT,
    url VARCHAR(255) NOT NULL,
    hash VARCHAR(14) NOT NULL,
    created_date DATETIME NOT NULL,
    expired_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    sent_date DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    PRIMARY KEY (id)
    );

CREATE TABLE processed_url (
    id INT AUTO_INCREMENT NOT NULL,
    processed_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    url VARCHAR(255) NOT NULL,
    created_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    PRIMARY KEY (id)
);