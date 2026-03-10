<?php

namespace App\Helpers;

class ExportHelper
{
    public static function generateUsersExcelExport($data, $filename) {
        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>' . htmlspecialchars($filename) . '</title>';
        $html .= '<style>';
        $html .= 'table { border-collapse: collapse; width: 100%; } ';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } ';
        $html .= 'th { background-color: #f2f2f2; font-weight: bold; } ';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '<h1>' . $filename . '</h1>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body>';
        $html .= '</html>';
        return $html;
    }

    public static function generateUsersPDFExport($data, $filename) {
        // Generate PDF-optimized HTML document
        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>' . htmlspecialchars($filename) . '</title>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 20px; }';
        $html .= 'table { border-collapse: collapse; width: 100%; } ';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } ';
        $html .= 'th { background-color: #f2f2f2; font-weight: bold; } ';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '<h1>' . $filename . '</h1>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body>';
        $html .= '</html>';
        return $html;
    }

    public static function generateUsersWordExport($data, $filename) {
        // Word-compatible HTML
        $headers = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Employee ID', 'Department', 'Position', 'Status', 'Role', 'Hire Date', 'Created At'];

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office">';
        $html .= '<head>';
        $html .= '<meta charset="utf-8">';
        $html .= '<title>' . htmlspecialchars($filename) . '</title>';
        $html .= '<style>';
        $html .= 'table { border-collapse: collapse; width: 100%; } ';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } ';
        $html .= 'th { background-color: #f2f2f2; font-weight: bold; } ';
        $html .= '</style>';
        $html .= '</head>';
        $html .= '<body>';
        $html .= '<h1>' . $filename . '</h1>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $key => $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body>';
        $html .= '</html>';
        return $html;
    }
}
