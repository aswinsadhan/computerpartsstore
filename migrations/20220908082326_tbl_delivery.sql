-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_delivery (
    delivery_id int PRIMARY KEY AUTO_INCREMENT,
    payment_id int NOT NULL,
    delivery_date  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES tbl_payment(payment_id)
);
-- migrate:down
DROP TABLE tbl_delivery;
