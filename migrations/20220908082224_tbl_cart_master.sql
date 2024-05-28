-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_cart_master (
    cart_master_id int PRIMARY KEY AUTO_INCREMENT,
    customer_id int NOT NULL,
    status varchar(20) NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES tbl_customer(customer_id)
);


-- migrate:down
DROP TABLE tbl_cart_master;
