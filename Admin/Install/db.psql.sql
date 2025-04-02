CREATE SEQUENCE billing_bill_sequence;

CREATE OR REPLACE FUNCTION update_billing_bill_sequence()
    RETURNS TRIGGER AS
    $$
    BEGIN
        NEW.billing_bill_sequence = nextval('billing_bill_sequence') WHERE billing_bill_unit = NEW.billing_bill_unit;
        RETURN NEW;
    END;
    $$
LANGUAGE plpgsql;

CREATE TRIGGER update_sequence_trigger
    BEFORE INSERT ON billing_bill
    FOR EACH ROW
    EXECUTE FUNCTION update_billing_bill_sequence();
