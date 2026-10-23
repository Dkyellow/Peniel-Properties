import pikepdf

input_file = "ECD B HOLIDAY WORK TERM 2.pdf"
output_file = "unlocked.pdf"

with pikepdf.open(input_file) as pdf:
    pdf.save(output_file)

print(f"Done! Saved as: {output_file}")