#include <iostream>
#include <vector>

using namespace std;

/**
 * Código C++ corregido - Versión CORREGIDA
 * Este código muestra las correcciones aplicadas a los errores del código original
 */

/**
 * Función que calcula el promedio de un vector de números
 * CORRIGIDO: Manejo de vector vacío
 */
double calcularPromedio(vector<int> numeros) {
    // CORRECCIÓN: Verificar si el vector está vacío
    if(numeros.empty()) {
        cout << "Error: No se puede calcular el promedio de un vector vacío" << endl;
        return 0.0;
    }
    
    int suma = 0;
    for(int i = 0; i < numeros.size(); i++) {
        suma += numeros[i];
    }
    
    double promedio = static_cast<double>(suma) / numeros.size();  // Cast para precisión
    return promedio;
}

/**
 * Función que muestra la tabla de multiplicar
 * CORRIGIDO: Rango y validación
 */
void mostrarTablaMultiplicar(int numero) {
    // CORRECCIÓN: Validar número de entrada
    if(numero <= 0) {
        cout << "Error: El número debe ser mayor que 0" << endl;
        return;
    }
    
    cout << "Tabla de multiplicar del " << numero << ":" << endl;
    
    // CORRECCIÓN: Rango de 1 a 10 (no incluir 0)
    for(int i = 1; i <= 10; i++) {  // Va de 1 a 10
        int resultado = numero * i;
        cout << numero << " x " << i << " = " << resultado << endl;
    }
}

/**
 * Función que accede a elementos de un array de forma segura
 * CORRIGIDO: Verificación de límites
 */
void procesarArray() {
    int numeros[5] = {1, 2, 3, 4, 5};
    int tamaño = 5;  // CORRECCIÓN: Definir explícitamente el tamaño
    
    cout << "Procesando array de forma segura:" << endl;
    
    // CORRECCIÓN: Verificar límites del array
    for(int i = 0; i < tamaño; i++) {  // i < tamaño, no i <= tamaño
        cout << "Elemento " << i << ": " << numeros[i] << endl;
    }
}

/**
 * Función que demuestra el manejo seguro de vectores
 * AÑADIDO: Nueva función para demostrar buenas prácticas
 */
void procesarVectorSeguro() {
    vector<int> numeros = {10, 20, 30, 40, 50};
    
    cout << "\nProcesando vector de forma segura:" << endl;
    
    // Método seguro usando .size()
    for(size_t i = 0; i < numeros.size(); i++) {
        cout << "Elemento " << i << ": " << numeros[i] << endl;
    }
    
    // Método alternativo con iteradores (más moderno)
    cout << "\nUsando iteradores (C++ moderno):" << endl;
    for(const auto& numero : numeros) {
        cout << "Valor: " << numero << endl;
    }
}

/**
 * Función principal corregida
 */
int main() {
    cout << "=== Demostrando código corregido ===" << endl;
    
    // CORRECCIÓN: Usar vector con datos en lugar de vector vacío
    vector<int> listaNumeros = {15, 25, 35, 45, 55};
    double promedio = calcularPromedio(listaNumeros);
    cout << "Promedio: " << promedio << endl;
    
    // CORRECCIÓN: Usar número válido para la tabla
    cout << "\n--- Tabla de multiplicar ---" << endl;
    mostrarTablaMultiplicar(7);
    
    // CORRECCIÓN: Procesar array de forma segura
    cout << "\n--- Procesamiento de array ---" << endl;
    procesarArray();
    
    // AÑADIDO: Demostrar procesamiento seguro de vectores
    procesarVectorSeguro();
    
    // Demostrar manejo de errores
    cout << "\n=== Demostrando manejo de errores ===" << endl;
    
    // Probar con vector vacío
    vector<int> vectorVacio;
    calcularPromedio(vectorVacio);
    
    // Probar con número inválido
    mostrarTablaMultiplicar(0);
    mostrarTablaMultiplicar(-5);
    
    return 0;
}