#!/usr/bin/env python3
import re
import os
import glob

# List of component files to check
components = [
    'alert',
    'avatar',
    'badge',
    'banner-carousel',
    'button',
    'card',
    'checkbox',
    'dropdown-item',
    'dropdown-select',
    'dropdown',
    'icon',
    'image-upload',
    'input',
    'modal',
    'product-image',
    'radio',
    'select',
    'skeleton',
    'spinner-examples',
    'spinner',
    'system-clock',
    'textarea',
    'toast',
]

# Find all blade and PHP files
blade_files = glob.glob('resources/views/**/*.blade.php', recursive=True)
php_files = glob.glob('app/**/*.php', recursive=True)
all_files = blade_files + php_files

print("=== Checking Component Usage ===\n")

unused_components = []
usage_count = {}

for component in components:
    # Skip the component file itself
    component_file = f'resources/views/components/ui/{component}.blade.php'
    
    # Patterns to search for
    patterns = [
        f'<x-ui.{component}',  # Blade component usage
        f'x-ui.{component}',   # Alternative usage
    ]
    
    count = 0
    for filepath in all_files:
        if filepath == component_file:
            continue
            
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
                for pattern in patterns:
                    if pattern in content:
                        count += 1
                        break  # Count file only once
        except:
            pass
    
    usage_count[component] = count
    
    if count == 0:
        unused_components.append(component)
        print(f"❌ UNUSED: {component}.blade.php")
    elif count < 5:
        print(f"⚠️  LOW USAGE ({count} files): {component}.blade.php")
    else:
        print(f"✓ USED ({count} files): {component}.blade.php")

print(f"\n=== Summary ===")
print(f"Total components: {len(components)}")
print(f"Unused components: {len(unused_components)}")
print(f"Components with low usage (<5): {len([c for c, count in usage_count.items() if 0 < count < 5])}")

if unused_components:
    print(f"\n=== Unused Components (Safe to Delete) ===")
    for comp in unused_components:
        print(f"  - resources/views/components/ui/{comp}.blade.php")
