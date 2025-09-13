#!/usr/bin/env python3
"""
Generador de PDF para Tutorial de Programación Python y C++
"""

from reportlab.lib.pagesizes import letter, A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, PageBreak
from reportlab.platypus import Table, TableStyle, KeepTogether
from reportlab.lib.colors import black, blue, red, darkgreen, gray
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY, TA_LEFT
from reportlab.pdfgen import canvas
from reportlab.platypus.tableofcontents import TableOfContents
import os
from datetime import datetime

class PDFTutorialGenerator:
    def __init__(self):
        self.styles = getSampleStyleSheet()
        self.setup_custom_styles()
        
    def setup_custom_styles(self):
        """Configura estilos personalizados para el documento"""
        # Estilo para títulos principales
        self.styles.add(ParagraphStyle(
            name='MainTitle',
            parent=self.styles['Title'],
            fontSize=24,
            textColor=darkgreen,
            spaceAfter=30,
            alignment=TA_CENTER,
            fontName='Helvetica-Bold'
        ))
        
        # Estilo para subtítulos
        self.styles.add(ParagraphStyle(
            name='SubTitle',
            parent=self.styles['Heading1'],
            fontSize=18,
            textColor=blue,
            spaceAfter=20,
            spaceBefore=20,
            fontName='Helvetica-Bold'
        ))
        
        # Estilo para código
        self.styles.add(ParagraphStyle(
            name='CodeStyle',
            parent=self.styles['Normal'],
            fontSize=9,
            fontName='Courier',
            textColor=black,
            leftIndent=20,
            rightIndent=20,
            spaceBefore=10,
            spaceAfter=10
        ))
        
        # Estilo para preguntas
        self.styles.add(ParagraphStyle(
            name='Question',
            parent=self.styles['Normal'],
            fontSize=12,
            textColor=red,
            fontName='Helvetica-Bold',
            spaceBefore=15,
            spaceAfter=10
        ))
        
        # Estilo para respuestas
        self.styles.add(ParagraphStyle(
            name='Answer',
            parent=self.styles['Normal'],
            fontSize=11,
            textColor=darkgreen,
            fontName='Helvetica',
            leftIndent=30,
            spaceAfter=15
        ))

    def read_code_file(self, filepath):
        """Lee un archivo de código y retorna su contenido"""
        try:
            with open(filepath, 'r', encoding='utf-8') as file:
                return file.read()
        except FileNotFoundError:
            return f"Error: No se pudo encontrar el archivo {filepath}"

    def create_code_table(self, code_content, language):
        """Crea una tabla formateada para mostrar código"""
        lines = code_content.split('\n')
        data = []
        
        # Encabezado
        data.append([f"Código {language}", ""])
        
        # Contenido del código
        for i, line in enumerate(lines, 1):
            # Escapar caracteres especiales para ReportLab
            line = line.replace('<', '&lt;').replace('>', '&gt;').replace('&', '&amp;')
            data.append([f"{i:3d}", line])
        
        table = Table(data, colWidths=[0.5*inch, 7*inch])
        table.setStyle(TableStyle([
            ('BACKGROUND', (0, 0), (-1, 0), gray),
            ('TEXTCOLOR', (0, 0), (-1, 0), black),
            ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
            ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
            ('FONTNAME', (0, 1), (-1, -1), 'Courier'),
            ('FONTSIZE', (0, 0), (-1, -1), 8),
            ('GRID', (0, 0), (-1, -1), 1, black),
            ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ]))
        
        return table

    def generate_pdf(self, output_path):
        """Genera el PDF completo del tutorial"""
        doc = SimpleDocTemplate(
            output_path,
            pagesize=A4,
            rightMargin=72,
            leftMargin=72,
            topMargin=72,
            bottomMargin=18
        )
        
        # Construir el contenido del documento
        story = []
        
        # Portada
        story.extend(self.create_cover_page())
        story.append(PageBreak())
        
        # Índice
        story.extend(self.create_table_of_contents())
        story.append(PageBreak())
        
        # Fase 1: Contador regresivo
        story.extend(self.create_phase1_content())
        story.append(PageBreak())
        
        # Fase 2: Corrección de errores
        story.extend(self.create_phase2_content())
        
        # Generar el PDF
        doc.build(story)
        print(f"PDF generado exitosamente: {output_path}")

    def create_cover_page(self):
        """Crea la página de portada"""
        story = []
        
        # Título principal
        story.append(Paragraph("Tutorial de Programación", self.styles['MainTitle']))
        story.append(Spacer(1, 0.5*inch))
        
        # Subtítulo
        story.append(Paragraph("Comparación entre Python y C++", self.styles['SubTitle']))
        story.append(Spacer(1, 0.3*inch))
        
        # Descripción
        description = """
        Este tutorial presenta una comparación detallada entre los lenguajes de programación 
        Python y C++, incluyendo ejemplos prácticos, medición de tiempos de ejecución, 
        manejo de errores y técnicas de depuración.
        """
        story.append(Paragraph(description, self.styles['Normal']))
        story.append(Spacer(1, 0.5*inch))
        
        # Contenido del tutorial
        content_list = """
        <b>Contenido del Tutorial:</b><br/>
        • Fase 1: Implementación de contador regresivo<br/>
        • Fase 2: Detección y corrección de errores<br/>
        • Comparación de rendimiento entre lenguajes<br/>
        • Análisis de mensajes de error y depuración<br/>
        """
        story.append(Paragraph(content_list, self.styles['Normal']))
        story.append(Spacer(1, 1*inch))
        
        # Información del autor y fecha
        footer_info = f"""
        <b>Proyecto JAM - Tutorial Educativo</b><br/>
        Corporación Uniremington, Sede Cali<br/>
        Fecha de creación: {datetime.now().strftime('%d de %B de %Y')}
        """
        story.append(Paragraph(footer_info, self.styles['Normal']))
        
        return story

    def create_table_of_contents(self):
        """Crea el índice del documento"""
        story = []
        
        story.append(Paragraph("Índice", self.styles['SubTitle']))
        story.append(Spacer(1, 0.3*inch))
        
        toc_content = """
        <b>1. Fase 1: Contador Regresivo</b><br/>
        &nbsp;&nbsp;&nbsp;&nbsp;1.1 Implementación en Python<br/>
        &nbsp;&nbsp;&nbsp;&nbsp;1.2 Implementación en C++<br/>
        &nbsp;&nbsp;&nbsp;&nbsp;1.3 Comparación de tiempos de ejecución<br/>
        &nbsp;&nbsp;&nbsp;&nbsp;1.4 Análisis de resultados<br/><br/>
        
        <b>2. Fase 2: Corrección de Errores</b><br/>
        &nbsp;&nbsp;&nbsp;&nbsp;2.1 Código Python con errores<br/>
        &nbsp;&nbsp;&nbsp;&nbsp;2.2 Código Python corregido<br/>
        &nbsp;&nbsp;&nbsp;&nbsp;2.3 Código C++ con errores<br/>
        &nbsp;&nbsp;&nbsp;&nbsp;2.4 Código C++ corregido<br/>
        &nbsp;&nbsp;&nbsp;&nbsp;2.5 Análisis de detección de errores<br/>
        &nbsp;&nbsp;&nbsp;&nbsp;2.6 Interpretación de mensajes de error<br/>
        """
        
        story.append(Paragraph(toc_content, self.styles['Normal']))
        
        return story

    def create_phase1_content(self):
        """Crea el contenido de la Fase 1"""
        story = []
        
        # Título de la fase
        story.append(Paragraph("Fase 1: Contador Regresivo", self.styles['SubTitle']))
        
        # Introducción
        intro = """
        En esta fase implementaremos un contador regresivo en ambos lenguajes para comparar 
        la sintaxis, facilidad de implementación y tiempo de ejecución.
        """
        story.append(Paragraph(intro, self.styles['Normal']))
        story.append(Spacer(1, 0.2*inch))
        
        # Implementación en Python
        story.append(Paragraph("1.1 Implementación en Python", self.styles['Heading2']))
        
        python_explanation = """
        El código Python utiliza la librería <i>time</i> para medir el tiempo de ejecución 
        y crear pausas. La sintaxis es simple y legible, con tipado dinámico.
        """
        story.append(Paragraph(python_explanation, self.styles['Normal']))
        story.append(Spacer(1, 0.1*inch))
        
        # Código Python
        python_code = self.read_code_file('/home/runner/work/proyecto-jam/proyecto-jam/tutorial/fase1/contador_python.py')
        story.append(self.create_code_table(python_code, "Python"))
        story.append(Spacer(1, 0.2*inch))
        
        # Implementación en C++
        story.append(Paragraph("1.2 Implementación en C++", self.styles['Heading2']))
        
        cpp_explanation = """
        El código C++ utiliza las librerías <i>chrono</i> y <i>thread</i> para medir tiempo 
        y crear pausas. Requiere más declaraciones explícitas pero ofrece mayor control.
        """
        story.append(Paragraph(cpp_explanation, self.styles['Normal']))
        story.append(Spacer(1, 0.1*inch))
        
        # Código C++
        cpp_code = self.read_code_file('/home/runner/work/proyecto-jam/proyecto-jam/tutorial/fase1/contador_cpp.cpp')
        story.append(self.create_code_table(cpp_code, "C++"))
        story.append(Spacer(1, 0.2*inch))
        
        # Comparación de tiempos
        story.append(Paragraph("1.3 Comparación de Tiempos de Ejecución", self.styles['Heading2']))
        
        # Pregunta
        story.append(Paragraph("¿Qué tan rápido se ejecutó cada versión?", self.styles['Question']))
        
        # Respuesta
        timing_answer = """
        <b>Resultados de ejecución:</b><br/>
        • <b>Python:</b> 1.0016 segundos<br/>
        • <b>C++:</b> 1.001 segundos (1001 milisegundos)<br/><br/>
        
        <b>Análisis:</b><br/>
        Ambos lenguajes muestran tiempos muy similares porque el tiempo de ejecución está dominado 
        por las pausas de 0.1 segundos (100ms) insertadas intencionalmente. En términos de tiempo 
        puro de procesamiento, C++ es ligeramente más rápido, pero la diferencia es mínima en este ejemplo.
        
        La diferencia real se vería en operaciones computacionalmente intensivas sin pausas artificiales, 
        donde C++ típicamente supera a Python debido a su compilación a código nativo.
        """
        story.append(Paragraph(timing_answer, self.styles['Answer']))
        
        return story

    def create_phase2_content(self):
        """Crea el contenido de la Fase 2"""
        story = []
        
        # Título de la fase
        story.append(Paragraph("Fase 2: Corrección de Errores", self.styles['SubTitle']))
        
        # Introducción
        intro = """
        En esta fase analizaremos errores comunes en ambos lenguajes, cuándo se detectan, 
        qué mensajes generan y cómo corregirlos.
        """
        story.append(Paragraph(intro, self.styles['Normal']))
        story.append(Spacer(1, 0.2*inch))
        
        # Código Python con errores
        story.append(Paragraph("2.1 Código Python con Errores (Original)", self.styles['Heading2']))
        
        python_error_code = self.read_code_file('/home/runner/work/proyecto-jam/proyecto-jam/tutorial/fase2/codigo_errores_python_original.py')
        story.append(self.create_code_table(python_error_code, "Python (con errores)"))
        story.append(Spacer(1, 0.2*inch))
        
        # Código Python corregido
        story.append(Paragraph("2.2 Código Python Corregido", self.styles['Heading2']))
        
        python_fixed_code = self.read_code_file('/home/runner/work/proyecto-jam/proyecto-jam/tutorial/fase2/codigo_errores_python_corregido.py')
        story.append(self.create_code_table(python_fixed_code, "Python (corregido)"))
        story.append(Spacer(1, 0.2*inch))
        
        # Código C++ con errores
        story.append(Paragraph("2.3 Código C++ con Errores (Original)", self.styles['Heading2']))
        
        cpp_error_code = self.read_code_file('/home/runner/work/proyecto-jam/proyecto-jam/tutorial/fase2/codigo_errores_cpp_original.cpp')
        story.append(self.create_code_table(cpp_error_code, "C++ (con errores)"))
        story.append(Spacer(1, 0.2*inch))
        
        # Código C++ corregido
        story.append(Paragraph("2.4 Código C++ Corregido", self.styles['Heading2']))
        
        cpp_fixed_code = self.read_code_file('/home/runner/work/proyecto-jam/proyecto-jam/tutorial/fase2/codigo_errores_cpp_corregido.cpp')
        story.append(self.create_code_table(cpp_fixed_code, "C++ (corregido)"))
        story.append(Spacer(1, 0.2*inch))
        
        # Análisis de errores
        story.append(Paragraph("2.5 Análisis de Detección de Errores", self.styles['Heading2']))
        
        # Pregunta 1
        story.append(Paragraph("¿Qué pasa si hay un error en el código?", self.styles['Question']))
        error_answer1 = """
        <b>En Python:</b> Los errores de sintaxis se detectan al interpretar, los errores de lógica 
        en tiempo de ejecución. El programa se detiene y muestra un traceback detallado.<br/><br/>
        
        <b>En C++:</b> Los errores de sintaxis y tipos se detectan en compilación. Los errores de 
        lógica (como división por cero) causan fallos en tiempo de ejecución o comportamiento indefinido.
        """
        story.append(Paragraph(error_answer1, self.styles['Answer']))
        
        # Pregunta 2
        story.append(Paragraph("¿Cuándo se detectan los errores en cada lenguaje?", self.styles['Question']))
        error_answer2 = """
        <b>Python (Interpretado):</b><br/>
        • Errores de sintaxis: Al ejecutar la línea problemática<br/>
        • Errores de nombre/tipo: En tiempo de ejecución cuando se alcanza la línea<br/>
        • Ventaja: Desarrollo rápido, cambios inmediatos<br/>
        • Desventaja: Errores pueden pasar desapercibidos hasta la ejecución<br/><br/>
        
        <b>C++ (Compilado):</b><br/>
        • Errores de sintaxis/tipos: En tiempo de compilación (antes de ejecutar)<br/>
        • Errores de lógica: En tiempo de ejecución<br/>
        • Ventaja: Muchos errores se detectan antes de distribuir el programa<br/>
        • Desventaja: Proceso de compilación adicional
        """
        story.append(Paragraph(error_answer2, self.styles['Answer']))
        
        # Pregunta 3
        story.append(Paragraph("¿Qué mensaje de error da el compilador/intérprete?", self.styles['Question']))
        error_answer3 = """
        <b>Mensaje de error Python (observado):</b><br/>
        <font name="Courier">Error encontrado: name 'total_elementos' is not defined</font><br/><br/>
        
        <b>Mensaje de error C++ (observado):</b><br/>
        <font name="Courier">Floating point exception (core dumped)</font><br/><br/>
        
        Python proporciona mensajes más descriptivos que ayudan a identificar exactamente qué variable 
        o problema causó el error. C++ a menudo da mensajes más técnicos que requieren mayor experiencia 
        para interpretar.
        """
        story.append(Paragraph(error_answer3, self.styles['Answer']))
        
        # Pregunta 4
        story.append(Paragraph("¿Cómo se pueden interpretar y corregir estos errores?", self.styles['Question']))
        error_answer4 = """
        <b>Estrategias de corrección:</b><br/><br/>
        
        <b>1. Variables no definidas (Python):</b><br/>
        • Leer el mensaje: "name 'variable' is not defined"<br/>
        • Verificar ortografía de la variable<br/>
        • Asegurar que la variable se declara antes de usarse<br/>
        • Verificar el ámbito (scope) de la variable<br/><br/>
        
        <b>2. División por cero (C++):</b><br/>
        • "Floating point exception" indica operación matemática inválida<br/>
        • Verificar divisiones por cero<br/>
        • Añadir validaciones antes de operaciones matemáticas<br/>
        • Usar depuradores para encontrar la línea exacta<br/><br/>
        
        <b>3. Acceso fuera de límites:</b><br/>
        • Verificar índices de arrays/listas<br/>
        • Usar .size() o len() para límites<br/>
        • Implementar verificaciones de rango<br/><br/>
        
        <b>Mejores prácticas:</b><br/>
        • Validar entradas<br/>
        • Manejar casos especiales (listas vacías, valores nulos)<br/>
        • Usar herramientas de depuración<br/>
        • Escribir código defensivo con verificaciones
        """
        story.append(Paragraph(error_answer4, self.styles['Answer']))
        
        return story

def main():
    """Función principal que genera el PDF"""
    # Crear directorio para el PDF si no existe
    output_dir = "/home/runner/work/proyecto-jam/proyecto-jam/docs"
    os.makedirs(output_dir, exist_ok=True)
    
    # Ruta del archivo PDF de salida
    pdf_path = os.path.join(output_dir, "tutorial_programacion_python_cpp.pdf")
    
    # Generar el PDF
    generator = PDFTutorialGenerator()
    generator.generate_pdf(pdf_path)
    
    print(f"Tutorial PDF creado exitosamente en: {pdf_path}")

if __name__ == "__main__":
    main()