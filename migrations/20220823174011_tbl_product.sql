-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_product (
    product_id int PRIMARY KEY AUTO_INCREMENT,
    brand_id int NOT NULL,
    subcategory_id int NOT NULL,
    product_name varchar(100) NOT NULL,
    product_description TEXT NOT NULL,
    product_image MEDIUMBLOB NOT NULL,
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status boolean NOT NULL DEFAULT 1,
    FOREIGN KEY (brand_id) REFERENCES tbl_brand(brand_id),
    FOREIGN KEY (subcategory_id) REFERENCES tbl_subcategory(subcategory_id)
);

-- migrate:down
DROP TABLE tbl_product;
