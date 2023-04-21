CREATE TRIGGER update_billing_bill_sequence
BEFORE INSERT ON billing_bill
FOR EACH ROW BEGIN
    SET NEW.billing_bill_sequence = (
        SELECT COALESCE(MAX(billing_bill_sequence), 0) + 1
        FROM billing_bill
        WHERE billing_bill_unit = NEW.billing_bill_unit
            AND billing_bill_type = NEW.billing_bill_type
        LIMIT 1
    );
END;