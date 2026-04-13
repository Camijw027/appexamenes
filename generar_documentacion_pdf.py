from pathlib import Path
from datetime import datetime


ROOT = Path(r"C:\Users\User\Desktop\appexamenes")
APP = ROOT / "app"
SQL = ROOT / "sql"
OUT = ROOT / "Documentacion_Proyecto_App_Examenes.pdf"


def read_text(path):
    return Path(path).read_text(encoding="utf-8", errors="replace")


class PDFBuilder:
    def __init__(self):
        self.width = 595
        self.height = 842
        self.margin = 48
        self.pages = []
        self.page = []
        self.y = self.height - self.margin

    def _escape(self, text):
        return text.replace("\\", "\\\\").replace("(", "\\(").replace(")", "\\)")

    def _line_height(self, size):
        return size + 4

    def new_page(self):
        if self.page:
            self.pages.append("\n".join(self.page))
        self.page = []
        self.y = self.height - self.margin

    def ensure(self, height_needed):
        if self.y - height_needed < self.margin:
            self.new_page()

    def rect(self, x, y, w, h, fill=(1, 1, 1), stroke=(0, 0, 0), line_width=1):
        fy = self.height - y - h
        self.page.append(f"{line_width} w")
        self.page.append(f"{fill[0]:.3f} {fill[1]:.3f} {fill[2]:.3f} rg")
        self.page.append(f"{stroke[0]:.3f} {stroke[1]:.3f} {stroke[2]:.3f} RG")
        self.page.append(f"{x:.2f} {fy:.2f} {w:.2f} {h:.2f} re B")

    def text(self, x, y, text, size=12, font="Helvetica", color=(0, 0, 0)):
        text = self._escape(text)
        py = self.height - y
        self.page.append("BT")
        self.page.append(f"/{font} {size} Tf")
        self.page.append(f"{color[0]:.3f} {color[1]:.3f} {color[2]:.3f} rg")
        self.page.append(f"1 0 0 1 {x:.2f} {py:.2f} Tm")
        self.page.append(f"({text}) Tj")
        self.page.append("ET")

    def paragraph(self, text, size=12, font="Helvetica", color=(0, 0, 0), indent=0, leading=None):
        if leading is None:
            leading = self._line_height(size)
        max_width = self.width - self.margin * 2 - indent
        avg_char = max(size * 0.52, 4)
        words = text.split()
        lines = []
        current = ""
        for word in words:
            candidate = word if not current else current + " " + word
            if len(candidate) * avg_char <= max_width:
                current = candidate
            else:
                lines.append(current)
                current = word
        if current:
            lines.append(current)
        self.ensure(len(lines) * leading + 8)
        for line in lines:
            self.text(self.margin + indent, self.y, line, size=size, font=font, color=color)
            self.y -= leading
        self.y -= 4

    def title(self, text):
        self.ensure(40)
        self.text(self.margin, self.y, text, size=22, font="Helvetica-Bold", color=(0.12, 0.18, 0.33))
        self.y -= 30

    def heading(self, text):
        self.ensure(28)
        self.text(self.margin, self.y, text, size=16, font="Helvetica-Bold", color=(0.18, 0.18, 0.18))
        self.y -= 22

    def subheading(self, text):
        self.ensure(24)
        self.text(self.margin, self.y, text, size=13, font="Helvetica-Bold", color=(0.22, 0.22, 0.22))
        self.y -= 18

    def bullet_list(self, items, size=12):
        for item in items:
            self.paragraph("- " + item, size=size, indent=8)

    def code_block(self, title, code, highlight_lines=None, explanation=None):
        if highlight_lines is None:
            highlight_lines = []
        lines = code.splitlines()
        line_height = 11
        pad = 8
        block_height = pad * 2 + len(lines) * line_height + 22
        self.ensure(block_height + (50 if explanation else 0))
        x = self.margin
        y_top = self.y
        width = self.width - self.margin * 2
        self.rect(x, y_top - block_height + 8, width, block_height, fill=(0.96, 0.97, 0.99), stroke=(0.72, 0.75, 0.82))
        self.text(x + 10, self.y - 4, title, size=12, font="Helvetica-Bold", color=(0.16, 0.22, 0.35))
        code_y = self.y - 20
        for idx, line in enumerate(lines, start=1):
            if idx in highlight_lines:
                self.rect(x + 6, code_y - 8, width - 12, line_height + 3, fill=(1.0, 0.97, 0.72), stroke=(0.95, 0.88, 0.35), line_width=0.6)
            self.text(x + 12, code_y, f"{idx:>2}  {line}", size=8, font="Courier", color=(0.1, 0.1, 0.1))
            code_y -= line_height
        self.y -= block_height + 8
        if explanation:
            self.paragraph(explanation, size=11)

    def finish(self):
        if self.page:
            self.pages.append("\n".join(self.page))
            self.page = []
        objects = []

        def add_object(content):
            objects.append(content)
            return len(objects)

        font_helv = add_object("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>")
        font_bold = add_object("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>")
        font_courier = add_object("<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>")

        page_ids = []
        content_ids = []
        pages_id_placeholder = None

        for content in self.pages:
            stream = content.encode("latin-1", errors="replace")
            content_id = add_object(f"<< /Length {len(stream)} >>\nstream\n{stream.decode('latin-1', errors='replace')}\nendstream")
            content_ids.append(content_id)
            page_ids.append(None)

        pages_id_placeholder = add_object("")

        for i, content_id in enumerate(content_ids):
            page_obj = (
                f"<< /Type /Page /Parent {pages_id_placeholder} 0 R "
                f"/MediaBox [0 0 {self.width} {self.height}] "
                f"/Resources << /Font << /Helvetica {font_helv} 0 R /Helvetica-Bold {font_bold} 0 R /Courier {font_courier} 0 R >> >> "
                f"/Contents {content_id} 0 R >>"
            )
            page_ids[i] = add_object(page_obj)

        kids = " ".join(f"{pid} 0 R" for pid in page_ids)
        objects[pages_id_placeholder - 1] = f"<< /Type /Pages /Count {len(page_ids)} /Kids [{kids}] >>"
        catalog_id = add_object(f"<< /Type /Catalog /Pages {pages_id_placeholder} 0 R >>")

        pdf = bytearray()
        pdf.extend(b"%PDF-1.4\n%\xe2\xe3\xcf\xd3\n")
        offsets = [0]
        for idx, obj in enumerate(objects, start=1):
            offsets.append(len(pdf))
            pdf.extend(f"{idx} 0 obj\n".encode("latin-1"))
            pdf.extend(obj.encode("latin-1", errors="replace"))
            pdf.extend(b"\nendobj\n")
        xref_pos = len(pdf)
        pdf.extend(f"xref\n0 {len(objects)+1}\n".encode("latin-1"))
        pdf.extend(b"0000000000 65535 f \n")
        for off in offsets[1:]:
            pdf.extend(f"{off:010d} 00000 n \n".encode("latin-1"))
        pdf.extend(
            f"trailer\n<< /Size {len(objects)+1} /Root {catalog_id} 0 R >>\nstartxref\n{xref_pos}\n%%EOF".encode("latin-1")
        )
        OUT.write_bytes(pdf)


def numbered_snippet(path, start, end):
    lines = read_text(path).splitlines()
    return "\n".join(lines[start - 1:end])


def build():
    pdf = PDFBuilder()
    pdf.new_page()

    today = datetime.now().strftime("%Y-%m-%d %H:%M")
    pdf.title("Documentacion tecnica del proyecto App Examenes")
    pdf.paragraph(
        "Este documento explica de forma detallada la estructura, el flujo funcional, las tablas de base de datos y los archivos mas importantes del proyecto. "
        "La idea es que sirva tanto para sustentar el trabajo como para estudiar el codigo."
    )
    pdf.paragraph(f"Fecha de generacion del documento: {today}.")

    pdf.heading("1. Objetivo general del sistema")
    pdf.paragraph(
        "El sistema permite que un profesor gestione alumnos, cree examenes con tres preguntas, asigne esos examenes a estudiantes y consulte resultados. "
        "Por el lado del alumno, el sistema permite iniciar sesion con cedula y apellido, ver los examenes asignados, responderlos y enviar las respuestas para recibir una calificacion."
    )
    pdf.bullet_list([
        "Rol profesor: administra alumnos, examenes, asignaciones y resultados.",
        "Rol alumno: se autentica, visualiza examenes pendientes y los resuelve.",
        "Base de datos: guarda alumnos, examenes, preguntas, asignaciones y resultados.",
        "Escala de evaluacion: 1 Deficiente, 2 Aceptable, 3 Excelente.",
    ])

    pdf.heading("2. Estructura de carpetas")
    pdf.bullet_list([
        "app/: contiene toda la aplicacion PHP.",
        "app/alumno/: login, panel y presentacion de examenes.",
        "app/alumnos/: CRUD de alumnos y asignacion de examenes.",
        "app/examenes/: creacion, listado, visualizacion, calificacion y resultados.",
        "app/profesor/: panel principal del profesor.",
        "sql/: esquema de base de datos y datos de respaldo.",
    ])

    pdf.heading("3. Flujo general del sistema")
    pdf.bullet_list([
        "Paso 1: la portada pregunta si se ingresa como alumno o profesor.",
        "Paso 2: el alumno valida su identidad con cedula y apellido.",
        "Paso 3: el profesor puede crear examenes y asignarlos a alumnos.",
        "Paso 4: el alumno responde un examen asignado.",
        "Paso 5: el sistema calcula la nota usando la escala 1 a 3.",
        "Paso 6: el resultado se guarda y el profesor puede filtrarlo por nota.",
    ])

    pdf.heading("4. Archivo principal y navegacion")
    pdf.subheading("4.1 Portada del sistema")
    pdf.code_block(
        "Figura 1. app/index.php",
        numbered_snippet(APP / "index.php", 1, 21),
        highlight_lines=[2, 13, 16],
        explanation=(
            "La linea 2 incluye la conexion a la base. Las lineas 13 a 16 muestran los dos roles del sistema. "
            "Esta pagina funciona como puerta de entrada y simplifica el flujo para el usuario final."
        ),
    )

    pdf.subheading("4.2 Helper de apoyo")
    pdf.code_block(
        "Figura 2. app/helpers.php",
        numbered_snippet(APP / "helpers.php", 1, 17),
        highlight_lines=[3, 8, 13, 17],
        explanation=(
            "La funcion e() protege la salida HTML. La funcion obtenerEscala() traduce la cantidad de respuestas correctas a una nota formal y a su descripcion."
        ),
    )

    pdf.heading("5. Base de datos y modelo relacional")
    pdf.paragraph(
        "El archivo basedatos.sql define las tablas base del proyecto. Este esquema soporta el CRUD de alumnos, la creacion de examenes, la asignacion a estudiantes y el almacenamiento de resultados."
    )
    pdf.code_block(
        "Figura 3. sql/basedatos.sql - Tablas",
        numbered_snippet(SQL / "basedatos.sql", 1, 50),
        highlight_lines=[4, 12, 19, 29, 39],
        explanation=(
            "Las lineas 4, 12 y 19 crean las entidades principales: alumnos, examenes y preguntas. "
            "La linea 29 introduce asignaciones para vincular alumnos con examenes. La linea 39 agrega resultados para guardar la nota final."
        ),
    )
    pdf.paragraph(
        "Relacion entre tablas: alumnos se relaciona con asignaciones; examenes se relaciona con preguntas y con asignaciones; "
        "cada asignacion puede tener un solo resultado. Esto permite saber que alumno presento que examen y con que nota."
    )

    pdf.heading("6. Modulo del alumno")
    pdf.subheading("6.1 Inicio de sesion del alumno")
    pdf.code_block(
        "Figura 4. app/alumno/login.php",
        numbered_snippet(APP / "alumno" / "login.php", 1, 49),
        highlight_lines=[1, 10, 12, 18, 20, 24],
        explanation=(
            "La sesion se inicia en la linea 1. En las lineas 10 a 14 se valida cedula y apellido usando una consulta preparada. "
            "Las lineas 18 a 20 guardan en sesion el identificador y el nombre del alumno para usarlo en el panel."
        ),
    )
    pdf.paragraph(
        "Linea importante 10: se usa prepare() para evitar errores de concatenacion directa en la consulta. "
        "Linea importante 18: la sesion permite reconocer al alumno en las siguientes paginas sin volver a pedir los datos."
    )

    pdf.subheading("6.2 Panel del alumno")
    pdf.code_block(
        "Figura 5. app/alumno/panel.php",
        numbered_snippet(APP / "alumno" / "panel.php", 1, 64),
        highlight_lines=[5, 11, 17, 32, 49, 56],
        explanation=(
            "La linea 5 protege el acceso al panel. Las lineas 11 a 17 consultan los examenes asignados al alumno actual. "
            "La tabla que comienza en la linea 32 muestra titulo, materia, fecha y estado. En la linea 49 aparece el enlace para presentar el examen."
        ),
    )
    pdf.paragraph(
        "Este archivo es importante porque cambia la aplicacion de un listado general a una experiencia personalizada. "
        "Cada alumno solo ve los examenes que realmente tiene asignados."
    )

    pdf.subheading("6.3 Presentacion del examen")
    pdf.code_block(
        "Figura 6. app/alumno/presentar.php",
        numbered_snippet(APP / "alumno" / "presentar.php", 1, 82),
        highlight_lines=[5, 17, 31, 43, 51, 56],
        explanation=(
            "La linea 17 valida que la asignacion pertenezca al alumno autenticado. "
            "La linea 31 evita presentar examenes ya enviados. "
            "Desde la linea 43 se imprimen las preguntas y en la linea 56 el formulario envia las respuestas al archivo de calificacion."
        ),
    )

    pdf.heading("7. Modulo del profesor")
    pdf.subheading("7.1 Panel del profesor")
    pdf.code_block(
        "Figura 7. app/profesor/panel.php",
        numbered_snippet(APP / "profesor" / "panel.php", 1, 16),
        highlight_lines=[10, 11, 12],
        explanation=(
            "Este panel concentra las tres acciones principales del rol profesor: gestionar alumnos, gestionar examenes y revisar resultados."
        ),
    )

    pdf.subheading("7.2 Listado y gestion de alumnos")
    pdf.code_block(
        "Figura 8. app/alumnos/listar.php",
        numbered_snippet(APP / "alumnos" / "listar.php", 1, 51),
        highlight_lines=[4, 16, 40, 41, 42],
        explanation=(
            "La linea 4 consulta los alumnos. Las lineas 40 a 42 agregan las acciones clave: editar, asignar examen y eliminar."
        ),
    )
    pdf.paragraph(
        "La accion nueva mas importante es Asignar examen, porque conecta el CRUD de alumnos con el modulo de evaluacion."
    )

    pdf.subheading("7.3 Asignacion de examenes")
    pdf.code_block(
        "Figura 9. app/alumnos/asignar.php",
        numbered_snippet(APP / "alumnos" / "asignar.php", 1, 118),
        highlight_lines=[11, 23, 31, 36, 47, 49, 57, 76, 88],
        explanation=(
            "La linea 11 busca el alumno. Las lineas 23 a 31 validan que no exista una asignacion duplicada. "
            "Las lineas 36 a 44 insertan la asignacion con estado pendiente. Las lineas 49 a 57 consultan las asignaciones existentes para mostrarlas en pantalla."
        ),
    )
    pdf.paragraph(
        "Linea importante 31: si ya existe una asignacion con el mismo alumno y examen, se evita duplicarla. "
        "Linea importante 36: la fecha se registra automaticamente para llevar trazabilidad."
    )

    pdf.subheading("7.4 Creacion de examenes con tres preguntas")
    pdf.code_block(
        "Figura 10. app/examenes/crear.php",
        numbered_snippet(APP / "examenes" / "crear.php", 1, 141),
        highlight_lines=[7, 10, 19, 34, 39, 45, 50, 92, 96, 111],
        explanation=(
            "Las lineas 7 a 16 recogen las tres preguntas desde el formulario. "
            "La linea 19 valida que el examen y todas las preguntas esten completas. "
            "La linea 34 inicia una transaccion para guardar examen y preguntas como una sola unidad. "
            "Las lineas 39 a 42 insertan el examen; las lineas 45 a 60 insertan las tres preguntas."
        ),
    )
    pdf.paragraph(
        "Este archivo resuelve uno de los requisitos mas importantes del ejercicio: crear el examen con sus preguntas en la misma pantalla. "
        "La transaccion es importante porque evita que quede un examen guardado sin sus preguntas."
    )

    pdf.heading("8. Calificacion y resultados")
    pdf.subheading("8.1 Calculo de la nota")
    pdf.code_block(
        "Figura 11. app/examenes/calificar.php",
        numbered_snippet(APP / "examenes" / "calificar.php", 1, 117),
        highlight_lines=[5, 13, 31, 39, 57, 64, 71, 78, 95, 96],
        explanation=(
            "La linea 5 valida que exista sesion de alumno y una asignacion valida. "
            "Las lineas 31 a 52 recorren cada pregunta y cuentan respuestas correctas. "
            "La linea 57 convierte el total de aciertos a la escala 1, 2 o 3. "
            "Las lineas 64 a 74 guardan el resultado, y la linea 78 marca la asignacion como presentada."
        ),
    )
    pdf.paragraph(
        "Lineas clave: la 57 usa obtenerEscala() para quitar el porcentaje y aplicar la escala pedida por el ejercicio. "
        "La linea 71 inserta el resultado definitivo. La linea 78 evita que el examen siga apareciendo como pendiente."
    )

    pdf.subheading("8.2 Consulta de resultados filtrables")
    pdf.code_block(
        "Figura 12. app/examenes/resultados.php",
        numbered_snippet(APP / "examenes" / "resultados.php", 1, 73),
        highlight_lines=[4, 5, 12, 15, 31, 35, 36, 37, 54],
        explanation=(
            "La linea 4 captura el filtro por calificacion. La consulta principal empieza en la linea 5 y une resultados, asignaciones, alumnos y examenes. "
            "La linea 12 agrega la condicion de filtro cuando el profesor selecciona una nota. "
            "Las lineas 35 a 37 definen el filtro visual con las tres categorias oficiales."
        ),
    )

    pdf.heading("9. Directorio de palabras, terminos y conceptos")
    glossary = [
        "Alumno: usuario que puede ingresar con cedula y apellido para responder examenes asignados.",
        "Profesor: usuario que administra alumnos, examenes y resultados.",
        "CRUD: conjunto de acciones Crear, Leer, Actualizar y Eliminar.",
        "Sesion: mecanismo temporal para recordar quien es el usuario autenticado entre paginas.",
        "Prepared Statement: consulta preparada que separa el SQL de los datos enviados.",
        "Transaccion: grupo de operaciones que deben completarse juntas o cancelarse juntas.",
        "Escala de evaluacion: conversion de aciertos a nota 1, 2 o 3.",
        "Asignacion: relacion entre un alumno y un examen especifico.",
        "Resultado: registro final con respuestas correctas, nota y descripcion.",
        "Estado pendiente: examen asignado que aun no ha sido presentado.",
        "Estado presentado: examen ya resuelto por el alumno.",
        "Foreign Key: relacion entre tablas para mantener integridad de datos.",
        "INNER JOIN: forma de unir tablas relacionadas en una consulta SQL.",
        "Helper: archivo de apoyo con funciones reutilizables como e() u obtenerEscala().",
        "HTML escaping: proceso de proteger la salida para que no rompa la pagina.",
    ]
    pdf.bullet_list(glossary, size=11)

    pdf.heading("10. Explicacion linea por linea de las lineas mas importantes")
    line_notes = [
        "app/index.php linea 13: abre el enlace para el rol alumno.",
        "app/index.php linea 16: abre el enlace para el rol profesor.",
        "app/alumno/login.php linea 10: prepara la consulta de validacion.",
        "app/alumno/login.php linea 18: guarda el id del alumno en la sesion.",
        "app/alumnos/asignar.php linea 23: busca si ya existe una asignacion igual.",
        "app/alumnos/asignar.php linea 36: inserta una nueva asignacion pendiente.",
        "app/examenes/crear.php linea 34: inicia la transaccion del examen.",
        "app/examenes/crear.php linea 39: inserta el encabezado del examen.",
        "app/examenes/crear.php linea 45: prepara la insercion de preguntas.",
        "app/examenes/calificar.php linea 39: compara la respuesta del alumno con la correcta.",
        "app/examenes/calificar.php linea 57: traduce aciertos en nota y descripcion.",
        "app/examenes/calificar.php linea 71: guarda el resultado final.",
        "app/examenes/calificar.php linea 78: cambia el estado de la asignacion a presentado.",
        "app/examenes/resultados.php linea 12: activa el filtro por nota.",
    ]
    pdf.bullet_list(line_notes, size=11)

    pdf.heading("11. Conclusiones")
    pdf.paragraph(
        "El proyecto quedo estructurado para cubrir el flujo completo del ejercicio: ingreso por roles, gestion de alumnos, creacion de examenes con tres preguntas, asignacion a estudiantes, presentacion del examen, guardado de la nota y consulta filtrada de resultados."
    )
    pdf.paragraph(
        "Desde el punto de vista tecnico, las piezas mas importantes son la tabla asignaciones, la tabla resultados, el helper de escala y el uso de consultas preparadas en varios puntos clave. "
        "Estas decisiones hacen que el sistema sea mas claro, mas mantenible y mas alineado con el requerimiento academico."
    )

    pdf.heading("12. Archivos explicados en este documento")
    pdf.bullet_list([
        "app/index.php",
        "app/helpers.php",
        "app/alumno/login.php",
        "app/alumno/panel.php",
        "app/alumno/presentar.php",
        "app/profesor/panel.php",
        "app/alumnos/listar.php",
        "app/alumnos/asignar.php",
        "app/examenes/crear.php",
        "app/examenes/calificar.php",
        "app/examenes/resultados.php",
        "sql/basedatos.sql",
    ])

    pdf.finish()


if __name__ == "__main__":
    build()
    print(str(OUT))
