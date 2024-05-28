-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_cart_child (
    cart_child_id int PRIMARY KEY AUTO_INCREMENT,
    cart_master_id int NOT NULL,
    product_id int NOT NULL,
    quantity int NOT NULL,
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES tbl_product(product_id),
    FOREIGN KEY (cart_master_id) REFERENCES tbl_cart_master(cart_master_id)
);


-- migrate:down
DROP TABLE tbl_cart_child;

