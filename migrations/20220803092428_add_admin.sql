-- migrate:up
INSERT INTO tbl_login (
    email,password,type,status ) 
VALUES ('admin@hardwareworld.xyz','$2y$10$rAr0HWWQrvYlE3R/G2E9teqZbXb3lKgSV6a9iAmbLhvE1nTZ/KeSG','admin',1);


-- migrate:down
DELETE FROM tbl_login;

