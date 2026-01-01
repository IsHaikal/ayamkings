-- Add keyword column to menu table if it doesn't exist
ALTER TABLE menu 
ADD COLUMN keyword VARCHAR(255) UNIQUE NOT NULL AFTER name;

-- Create unique index on keyword column
CREATE UNIQUE INDEX idx_menu_keyword ON menu(keyword);

-- If you already have data in the menu table, you can populate keywords like this:
-- UPDATE menu SET keyword = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name, ' ', '-'), '.', ''), ',', ''), '(', ''), ')', ''), '&', ''), '/', ''), ''', ''), '"', ''), '!', ''), '?', ''), ':', ''), ';', ''));
