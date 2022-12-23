ALTER TABLE user add date_start_span ENUM('specific_date', 'one_month', 'three_months', 'one_year') NULL DEFAULT NULL;
