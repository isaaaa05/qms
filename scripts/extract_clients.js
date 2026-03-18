#!/usr/bin/env node
/**
 * Extract client names from Excel file and generate SQL migration
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import xlsx from 'xlsx';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.join(__dirname, '..');

async function extractClientsFromExcel() {
  try {
    // Find the Excel file
    const files = fs.readdirSync(projectRoot);
    let excelFile = files.find(f => f.includes('client') && f.endsWith('.xls'));
    
    if (!excelFile) {
      console.error('[v0] Excel file not found in project root');
      process.exit(1);
    }

    const excelPath = path.join(projectRoot, excelFile);
    console.log(`[v0] Reading Excel file: ${excelPath}`);

    // Read the Excel file
    const workbook = xlsx.readFile(excelPath);
    const sheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[sheetName];
    const data = xlsx.utils.sheet_to_json(worksheet);

    console.log(`[v0] Found ${data.length} rows`);

    // Extract client information
    const clients = [];
    const seenNames = new Set();

    data.forEach((row, index) => {
      // Look for columns that might contain ID and name
      let id = null;
      let name = null;

      // Try different column names
      for (const key of Object.keys(row)) {
        const value = String(row[key]).trim();
        if (!value || value === 'ID' || value === 'Name' || value === 'NAMES') continue;

        // First column usually ID
        if (!id && /^\d+$/.test(value)) {
          id = value;
        }
        // Other columns might be name
        if (!name && value.length > 2 && /^[a-zA-Z\s,.-]+$/.test(value)) {
          name = value;
        }
      }

      if (name && !seenNames.has(name)) {
        clients.push({
          id: id || clients.length + 1,
          name: name
        });
        seenNames.add(name);
      }
    });

    console.log(`[v0] Extracted ${clients.length} unique clients`);
    console.log(JSON.stringify(clients, null, 2));

    return clients;
  } catch (error) {
    console.error('[v0] Error:', error.message);
    process.exit(1);
  }
}

extractClientsFromExcel();
