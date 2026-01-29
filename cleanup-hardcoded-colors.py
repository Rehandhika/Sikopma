#!/usr/bin/env python3
import re
import os
import glob

def find_hardcoded_colors(filepath):
    """Find hardcoded color classes in blade files"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Patterns to find
        patterns = [
            r'bg-green-\d+',
            r'bg-red-\d+',
            r'bg-blue-\d+',
            r'bg-yellow-\d+',
            r'bg-amber-\d+',
            r'text-green-\d+',
            r'text-red-\d+',
            r'text-blue-\d+',
            r'text-yellow-\d+',
            r'border-green-\d+',
            r'border-red-\d+',
            r'border-blue-\d+',
            r'border-yellow-\d+',
        ]
        
        found = []
        for pattern in patterns:
            matches = re.findall(pattern, content)
            if matches:
                found.extend(matches)
        
        return list(set(found))
    except Exception as e:
        return []

# Find all blade files
blade_files = glob.glob('resources/views/**/*.blade.php', recursive=True)

# Exclude public and vendor views
blade_files = [f for f in blade_files if 'public' not in f and 'vendor' not in f]

print("=== Files with Hardcoded Colors ===\n")

files_with_colors = {}
for filepath in blade_files:
    colors = find_hardcoded_colors(filepath)
    if colors:
        files_with_colors[filepath] = colors

# Sort by number of hardcoded colors
sorted_files = sorted(files_with_colors.items(), key=lambda x: len(x[1]), reverse=True)

for filepath, colors in sorted_files:
    print(f"\n{filepath}")
    print(f"  Colors found: {', '.join(colors)}")

print(f"\n\n=== Summary ===")
print(f"Total files with hardcoded colors: {len(files_with_colors)}")
print(f"Total unique color classes: {len(set([c for colors in files_with_colors.values() for c in colors]))}")
