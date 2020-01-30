!bin/bash
for file in *; do mv "$file" `echo $file | tr '-' '_'` ; done
