#!/usr/bin/env python3
"""
Extract client names from Excel file and generate SQL insert statements
"""
import xlrd
import sys
import json

def extract_clients_from_excel(excel_file):
    """Extract client names from Excel file"""
    try:
        # Load the Excel workbook using xlrd for .xls format
        wb = xlrd.open_workbook(excel_file)
        ws = wb.sheet_by_index(0)
        
        clients = []
        
        # Iterate through rows, skip header (row 0)
        for row_idx in range(1, ws.nrows):
            # Get values from columns A (0) and B (1)
            client_id = str(ws.cell_value(row_idx, 0)).strip()
            client_name = str(ws.cell_value(row_idx, 1)).strip() if ws.ncols > 1 else None
            
            # Skip empty rows and header-like rows
            if client_name and client_name.upper() not in ['NAME OF CLIENT', '', 'NAMES']:
                clients.append({
                    'id': client_id,
                    'name': client_name
                })
        
        return clients
    except Exception as e:
        print(f"Error extracting clients: {e}", file=sys.stderr)
        return []

if __name__ == '__main__':
    import os
    import glob
    
    # Find the Excel file
    excel_files = glob.glob('/**/*clientlistshort*.xls', recursive=True)
    
    if excel_files:
        excel_path = excel_files[0]
    else:
        excel_path = '9044 clientlistshort031826135150350.xls'
    
    clients = extract_clients_from_excel(excel_path)
    
    if not clients:
        print("No clients found or error reading file", file=sys.stderr)
        sys.exit(1)
    
    # Output as JSON for easy parsing
    print(json.dumps(clients, indent=2))
    print(f"\n# Total clients: {len(clients)}", file=sys.stderr)
