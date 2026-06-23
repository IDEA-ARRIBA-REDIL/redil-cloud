import re

with open('storage/app/archivos_desarrollador/campos_informe_excel.sql', 'r') as f:
    sql = f.read()

# Find all tuples: (id, 'nombre_campo_bd', 'nombre_campo_informe', selector_id, 'tabla', 'raw_sql', 'eloquent_sql', orden)
pattern = r"\(\s*(\d+)\s*,\s*'([^']+)'\s*,\s*'([^']+)'\s*,\s*(\d+)\s*,\s*'([^']+)'\s*,\s*('[tf]'|NULL)\s*,\s*('[tf]'|NULL)\s*,\s*(\d+)\s*\)"

matches = re.findall(pattern, sql)
print("$campos = [")
for m in matches:
    raw_sql = 'true' if m[5] == "'t'" else ('false' if m[5] == "'f'" else 'null')
    eloquent_sql = 'true' if m[6] == "'t'" else ('false' if m[6] == "'f'" else 'null')
    tabla = m[4].replace('asistentes.', 'users.')
    print(f"    ['id' => {m[0]}, 'nombre_campo_bd' => '{m[1]}', 'nombre_campo_informe' => '{m[2]}', 'selector_id' => {m[3]}, 'tabla' => '{tabla}', 'raw_sql' => {raw_sql}, 'eloquent_sql' => {eloquent_sql}, 'orden' => {m[7]}],")
print("];")

