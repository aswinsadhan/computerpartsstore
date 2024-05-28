-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_purchase_child (
    purchase_child_id int PRIMARY KEY AUTO_INCREMENT,
    purchase_master_id int NOT NULL,
    product_id int NOT NULL,
    cost_price int NOT NULL,
    selling_price int NOT NULL,
    quantity int NOT NULL,
    FOREIGN KEY (purchase_master_id) REFERENCES tbl_purchase_master(purchase_master_id),
    FOREIGN KEY (product_id) REFERENCES tbl_product(product_id)
);


-- migrate:down
DROP TABLE tbl_purchase_child;
