-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_purchase_master (
    purchase_master_id int PRIMARY KEY AUTO_INCREMENT,
    staff_id int NOT NULL,
    vendor_id int NOT NULL, 
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status boolean NOT NULL DEFAULT 1,
    FOREIGN KEY (staff_id) REFERENCES tbl_staff(staff_id),
    FOREIGN KEY (vendor_id) REFERENCES tbl_vendor(vendor_id)
);


-- migrate:down
DROP TABLE tbl_purchase_master;
