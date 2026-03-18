#!/usr/bin/env python3
"""
Extract client names from Excel file and generate SQL insert statements
"""
import openpyxl
import sys
import json

def extract_clients_from_excel(excel_file):
    """Extract client names from Excel file"""
    try:
        # Load the Excel workbook
        wb = openpyxl.load_workbook(excel_file)
        ws = wb.active
        
        clients = []
        
        # Assuming the first column contains client IDs and second column contains names
        # Skip the header row (row 1)
        for row_idx, row in enumerate(ws.iter_rows(min_row=2, values_only=True), start=2):
            if not row or not row[0]:
                continue
            
            # Column A: ID, Column B: Name
            client_id = str(row[0]).strip() if row[0] else None
            client_name = str(row[1]).strip() if len(row) > 1 and row[1] else None
            
            if client_name and client_name.upper() != 'NAME OF CLIENT':
                clients.append({
                    'id': client_id,
                    'name': client_name
                })
        
        return clients
    except Exception as e:
        print(f"Error extracting clients: {e}", file=sys.stderr)
        return []

if __name__ == '__main__':
    excel_path = '/vercel/share/v0-project/9044 clientlistshort031826135150350.xls'
    
    clients = extract_clients_from_excel(excel_path)
    
    if not clients:
        print("No clients found or error reading file", file=sys.stderr)
        sys.exit(1)
    
    # Output as JSON for easy parsing
    print(json.dumps(clients, indent=2))
    print(f"\n# Total clients: {len(clients)}", file=sys.stderr)
