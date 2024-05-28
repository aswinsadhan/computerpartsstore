-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_customer (
    customer_id int PRIMARY KEY AUTO_INCREMENT,
    email varchar(50) NOT NULL,
    customer_fname varchar(15) NOT NULL,
    customer_lname varchar(15) NOT NULL,
    customer_house_name varchar(20) NOT NULL,
    customer_street varchar(20) NOT NULL,
    customer_city varchar(20) NOT NULL,
    customer_state varchar(20) NOT NULL,
    customer_pincode varchar(7) NOT NULL,
    customer_phone varchar(10) NOT NULL,
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES tbl_login(email)
);


-- migrate:down
DROP TABLE tbl_customer;
