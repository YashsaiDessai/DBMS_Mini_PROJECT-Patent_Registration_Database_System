/*1 — DDL (examples)*/
-- create
CREATE TABLE sample_ddl_test (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- alter add column
ALTER TABLE sample_ddl_test ADD COLUMN note TEXT NULL;

-- create index
CREATE INDEX idx_sample_name ON sample_ddl_test (name);

-- drop
DROP TABLE IF EXISTS sample_ddl_test;

/*2 — DML (examples)*/
-- insert
INSERT INTO applicants (name, email, phone) VALUES ('A Test', 'a@test.com', '9999999999');

-- update
UPDATE applicants SET phone = '8888888888' WHERE email = 'a@test.com';

-- delete
DELETE FROM applicants WHERE email = 'a@test.com';

-- truncate (remove all rows)
TRUNCATE TABLE review_audit;

/*3 — SELECT with different clauses*/
-- basic select and distinct
SELECT DISTINCT applicant_id FROM patents;

-- WHERE with operators
SELECT * FROM patents WHERE filing_date BETWEEN '2024-01-01' AND '2024-12-31';

-- LIKE and pattern
SELECT * FROM applicants WHERE name LIKE 'John%';

-- ORDER BY + LIMIT + OFFSET
SELECT * FROM patents ORDER BY filing_date DESC LIMIT 10 OFFSET 5;

-- selecting specific columns (PROJECT)
SELECT title, application_number FROM patents WHERE applicant_id IS NOT NULL;

-- using aggregate + GROUP BY (already had, repeated)
SELECT patent_id, COUNT(*) AS cnt, SUM(amount) AS total FROM fees GROUP BY patent_id HAVING SUM(amount) > 1000;

/*4 — GROUP BY functions (avg, count, max, min, sum)*/
SELECT patent_id,
       COUNT(*) AS payments,
       SUM(amount) AS total_paid,
       AVG(amount) AS avg_payment,
       MIN(amount) AS min_payment,
       MAX(amount) AS max_payment
FROM fees
GROUP BY patent_id;

/*5 — Nested queries & subqueries (examples)*/
-- nested non-correlated
SELECT * FROM patents WHERE id IN (SELECT patent_id FROM fees WHERE amount > 1000);

-- correlated subquery: find patents whose last payment is recent
SELECT p.* FROM patents p
WHERE (SELECT MAX(payment_date) FROM fees f WHERE f.patent_id = p.id) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY);

/*6 — Set operators (UNION / INTERSECT / MINUS)*/
-- UNION (works in MySQL)
SELECT email FROM applicants WHERE email IS NOT NULL
UNION
SELECT email FROM examiners WHERE email IS NOT NULL;

-- INTERSECT: MySQL older versions do not support INTERSECT; emulate with INNER JOIN or EXISTS
-- Find emails present in both tables (INTERSECT)
SELECT a.email
FROM applicants a
WHERE a.email IS NOT NULL
  AND EXISTS (SELECT 1 FROM examiners e WHERE e.email = a.email);

-- MINUS / EXCEPT: find emails in applicants but not in examiners
SELECT a.email
FROM applicants a
WHERE NOT EXISTS (SELECT 1 FROM examiners e WHERE e.email = a.email);

/*7 — Logical operations & operators*/
-- use AND / OR / NOT / IN / BETWEEN
SELECT * FROM patents
WHERE (application_number LIKE 'PAT%' OR title LIKE '%Irrigation%')
  AND filing_date BETWEEN '2000-01-01' AND '2025-12-31'
  AND applicant_id IN (1,2,3)
  AND NOT (description IS NULL);

/*8 — Various types of JOINS*/
-- INNER JOIN (only matching rows)
SELECT p.title, a.name AS applicant
FROM patents p
INNER JOIN applicants a ON p.applicant_id = a.id;

-- LEFT JOIN (all left rows)
SELECT p.title, a.name AS applicant
FROM patents p
LEFT JOIN applicants a ON p.applicant_id = a.id;

-- RIGHT JOIN (all right rows) — MySQL supports RIGHT JOIN
SELECT p.title, a.name AS applicant
FROM patents p
RIGHT JOIN applicants a ON p.applicant_id = a.id;

-- FULL OUTER JOIN: MySQL doesn't support directly — emulate with UNION
SELECT p.id, p.title, a.name
FROM patents p LEFT JOIN applicants a ON p.applicant_id = a.id
UNION
SELECT p.id, p.title, a.name
FROM patents p RIGHT JOIN applicants a ON p.applicant_id = a.id;

-- CROSS JOIN
SELECT p.title, e.name
FROM patents p
CROSS JOIN examiners e
LIMIT 20;

-- SELF JOIN (example: find patents that share same applicant)
SELECT p1.id AS p1, p2.id AS p2, p1.title, p2.title
FROM patents p1
JOIN patents p2 ON p1.applicant_id = p2.applicant_id AND p1.id <> p2.id;

/*9 — VIEWS & TRIGGERS (examples)*/
-- VIEW
CREATE OR REPLACE VIEW patent_summary AS
SELECT p.id, p.title, a.name AS applicant, COALESCE(SUM(f.amount),0) AS total_fees
FROM patents p
LEFT JOIN applicants a ON p.applicant_id = a.id
LEFT JOIN fees f ON f.patent_id = p.id
GROUP BY p.id, p.title, a.name;

-- TRIGGER: when fees inserted, update status table (example)
DELIMITER $$
CREATE TRIGGER trg_after_fee_insert
AFTER INSERT ON fees
FOR EACH ROW
BEGIN
  -- insert status row
  INSERT INTO status (patent_id, status_text) VALUES (NEW.patent_id, CONCAT('Payment recorded: ', NEW.amount));
END$$
DELIMITER ;

/*10 — Mini project (connect front-end & backend)*/

You already have the full project; run these to populate demo data:

INSERT INTO applicants (name,email,phone) VALUES ('Yashsai Dessai','y@ex.com','9999');
INSERT INTO patents (title, application_number, filing_date, applicant_id) VALUES ('Auto Irrigation','A-238','2025-10-03',1);
INSERT INTO examiners (name,email) VALUES ('Dr A','a@exam.com');

-- add a fee and check trigger
INSERT INTO fees (patent_id, amount, payment_date, payment_mode) VALUES (1, 500.00, CURDATE(), 'Online');
