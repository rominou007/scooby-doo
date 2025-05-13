-- modif de la bdd pour la partie planning

CREATE TABLE plannings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    module_id INT,
    start_time DATETIME,
    end_time DATETIME
);
