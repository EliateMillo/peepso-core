#!/bin/bash

# Iterate over all .mo files in the directory
for file in *.mo; do
    # Extract filename without extension
    filename=$(basename -- "$file" .mo)

    # Rename the file
    mv "$file" "${filename#peepso-theme-gecko-}.mo"
done

# Iterate over all .po files in the directory
for file in *.po; do
    # Extract filename without extension
    filename=$(basename -- "$file" .po)

    # Rename the file
    mv "$file" "${filename#peepso-theme-gecko-}.po"
done