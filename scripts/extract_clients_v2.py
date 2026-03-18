#!/usr/bin/env python3
"""
Extract client names from Excel file
"""
import xlrd
import sys
import json
import os

def extract_clients_from_excel(excel_file):
    """Extract client names from Excel file"""
    try:
        # Load the Excel workbook using xlrd for .xls format
        wb = xlrd.open_workbook(excel_file, encoding_override='utf-8')
        ws = wb.sheet_by_index(0)
        
        clients = []
        
        # Iterate through rows, skip header (row 0)
        for row_idx in range(1, ws.nrows):
            # Get values from columns A (0) and B (1)
            try:
                client_id = str(ws.cell_value(row_idx, 0)).strip()
                client_name = str(ws.cell_value(row_idx, 1)).strip() if ws.ncols > 1 else None
            except:
                continue
            
            # Skip empty rows and header-like rows
            if client_name and client_name and len(client_name) > 0:
                if client_name.upper() not in ['NAME OF CLIENT', 'NAMES', 'NAME']:
                    clients.append({
                        'id': client_id if client_id and client_id != '0' else len(clients) + 1,
                        'name': client_name
                    })
        
        return clients
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        return []

if __name__ == '__main__':
    # Try to find the Excel file in current directory or parent
    possible_paths = [
        '9044 clientlistshort031826135150350.xls',
        './9044 clientlistshort031826135150350.xls',
        '../9044 clientlistshort031826135150350.xls',
        'scripts/../9044 clientlistshort031826135150350.xls',
    ]
    
    excel_path = None
    for path in possible_paths:
        if os.path.exists(path):
            excel_path = path
            break
    
    if not excel_path:
        print("Error: Could not find Excel file", file=sys.stderr)
        sys.exit(1)
    
    print(f"[v0] Reading from: {excel_path}", file=sys.stderr)
    clients = extract_clients_from_excel(excel_path)
    
    if clients:
        print(json.dumps(clients, indent=2))
        print(f"\n# Total clients extracted: {len(clients)}", file=sys.stderr)
    else:
        print("No clients found", file=sys.stderr)
        sys.exit(1)
