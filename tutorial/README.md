# Tutorial de Programación Python y C++

Este directorio contiene un tutorial completo que compara Python y C++ a través de ejemplos prácticos y análisis de errores.

## Estructura del Tutorial

### Fase 1: Contador Regresivo
- **contador_python.py**: Implementación del contador regresivo en Python con medición de tiempo
- **contador_cpp.cpp**: Implementación del contador regresivo en C++ con medición de tiempo

### Fase 2: Corrección de Errores
- **codigo_errores_python_original.py**: Código Python con errores intencionados
- **codigo_errores_python_corregido.py**: Versión corregida del código Python
- **codigo_errores_cpp_original.cpp**: Código C++ con errores intencionados  
- **codigo_errores_cpp_corregido.cpp**: Versión corregida del código C++

## Documentos Generados

### PDF Tutorial Completo
- **docs/tutorial_programacion_python_cpp.pdf**: Documento PDF completo con:
  - Portada y índice
  - Explicaciones detalladas de cada fase
  - Código comentado línea por línea
  - Análisis de tiempos de ejecución
  - Respuestas a las preguntas planteadas
  - Análisis de errores y correcciones

## Cómo Ejecutar los Ejemplos

### Python
```bash
# Contador regresivo
python3 tutorial/fase1/contador_python.py

# Código con errores
python3 tutorial/fase2/codigo_errores_python_original.py

# Código corregido
python3 tutorial/fase2/codigo_errores_python_corregido.py
```

### C++
```bash
# Compilar y ejecutar contador regresivo
g++ -o contador_cpp tutorial/fase1/contador_cpp.cpp
./contador_cpp

# Compilar y ejecutar código con errores
g++ -o codigo_errores_original tutorial/fase2/codigo_errores_cpp_original.cpp
./codigo_errores_original

# Compilar y ejecutar código corregido
g++ -o codigo_errores_corregido tutorial/fase2/codigo_errores_cpp_corregido.cpp
./codigo_errores_corregido
```

## Generar el PDF

Para regenerar el PDF tutorial:
```bash
python3 generate_tutorial_pdf.py
```

## Preguntas Respondidas en el Tutorial

1. **¿Qué tan rápido se ejecutó cada versión?**
   - Python: ~1.0016 segundos
   - C++: ~1.001 segundos
   - Análisis comparativo detallado incluido

2. **¿Qué pasa si hay un error en el código?**
   - Comparación entre manejo de errores en Python vs C++
   - Ejemplos de errores comunes y sus efectos

3. **¿Cuándo se detectan los errores en cada lenguaje?**
   - Python: En tiempo de ejecución (interpretado)
   - C++: Muchos en tiempo de compilación (compilado)

4. **¿Qué mensaje de error da el compilador/intérprete?**
   - Ejemplos reales de mensajes de error capturados
   - Comparación de claridad y utilidad

5. **¿Cómo se pueden interpretar y corregir?**
   - Estrategias de depuración para cada lenguaje
   - Mejores prácticas de programación defensiva

## Características del Tutorial

- ✅ Código completamente comentado
- ✅ Explicaciones pedagógicas detalladas
- ✅ Ejemplos prácticos ejecutables
- ✅ Medición real de tiempos de ejecución
- ✅ Errores reales capturados y analizados
- ✅ Formato PDF profesional y bien estructurado
- ✅ Contenido en español
- ✅ Enfoque educativo comparativo

Este tutorial fue creado como material educativo para comprender las diferencias fundamentales entre programación interpretada (Python) y compilada (C++).