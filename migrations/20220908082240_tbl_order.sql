-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_order (
    order_id int PRIMARY KEY AUTO_INCREMENT,
    cart_master_id int NOT NULL,
    date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_master_id) REFERENCES tbl_cart_master(cart_master_id)
);



-- migrate:down
DROP TABLE tbl_order;

