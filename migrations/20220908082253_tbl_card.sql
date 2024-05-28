-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_card (
    card_id int PRIMARY KEY AUTO_INCREMENT,
    customer_id int NOT NULL,
    card_name varchar(30) NOT NULL,
    card_no varchar(16) NOT NULL,
    card_expiry DATE NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES tbl_customer(customer_id)
);


-- migrate:down
DROP TABLE tbl_card;
