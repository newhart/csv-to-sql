<?php

function csv_to_sql($csv_file, $sql_file, $table_name)
{
    // Open the CSV file for reading
    $csv_handle = fopen($csv_file, 'r');
    if (!$csv_handle) {
        die("Failed to open CSV file: $csv_file");
    }

    // Read the header row to get column names
    $header = fgetcsv($csv_handle);
    $column_count = count($header);

    // Determine column types
    $column_types = [];
    foreach ($header as $column) {
        $column_type = 'VARCHAR(255)';
        while ($row = fgetcsv($csv_handle)) {
            foreach ($row as $value) {
                if (is_numeric($value) && !strpos($value, '-')) {
                    $column_type = 'INTEGER';
                } elseif (strtotime($value) !== false) {
                    $column_type = 'DATE';
                }
            }
            break;
        }
        $column_types[] = $column_type;
        rewind($csv_handle);
    }

    // Generate the SQL statements
    $sql_handle = fopen($sql_file, 'w');
    if (!$sql_handle) {
        die("Failed to create SQL file: $sql_file");
    }

    while ($row = fgetcsv($csv_handle)) {
        $values = [];
        foreach ($row as $index => $value) {
            $escaped_value = addslashes($value);
            $values[] = $column_types[$index] === 'VARCHAR(255)' ? "'$escaped_value'" : $escaped_value;
        }
        $sql = "INSERT INTO $table_name VALUES (" . implode(', ', $values) . ");\n";
        fwrite($sql_handle, $sql);
    }

    // Close file handles
    fclose($csv_handle);
    fclose($sql_handle);
}

// Usage example
$csv_file = 'input.csv';  // Specify the path to your CSV file
$sql_file = 'output.sql'; // Specify the path to the output SQL file
$table_name = 'your_table';  // Specify the name of your table

csv_to_sql($csv_file, $sql_file, $table_name);
