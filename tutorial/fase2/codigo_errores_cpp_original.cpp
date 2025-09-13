#include <iostream>
#include <vector>

using namespace std;

/**
 * Código C++ con errores - Versión ORIGINAL (con errores)
 * Este código contiene errores intencionados para demostrar la detección y corrección
 */

/**
 * Función que calcula el promedio de un vector de números
 * ERRORES INCLUIDOS INTENCIONALMENTE
 */
double calcularPromedio(vector<int> numeros) {
    int suma = 0;
    
    // ERROR 1: No verificar si el vector está vacío (división por cero)
    for(int i = 0; i < numeros.size(); i++) {
        suma += numeros[i];
    }
    
    double promedio = suma / numeros.size();  // Falla si el vector está vacío
    return promedio;
}

/**
 * Función que muestra la tabla de multiplicar
 * ERRORES INCLUIDOS INTENCIONALMENTE
 */
void mostrarTablaMultiplicar(int numero) {
    cout << "Tabla de multiplicar del " << numero << ":" << endl;
    
    // ERROR 2: Rango incorrecto y acceso fuera de límites
    for(int i = 0; i <= 10; i++) {  // Va de 0 a 10, debería ser de 1 a 10
        int resultado = numero * i;
        cout << numero << " x " << i << " = " << resultado << endl;
    }
}

/**
 * Función que accede a elementos de un array
 * ERRORES INCLUIDOS INTENCIONALMENTE
 */
void procesarArray() {
    int numeros[5] = {1, 2, 3, 4, 5};
    
    // ERROR 3: Acceso fuera de los límites del array
    for(int i = 0; i <= 5; i++) {  // El índice 5 está fuera de límites
        cout << "Elemento " << i << ": " << numeros[i] << endl;
    }
}

/**
 * Función principal con múltiples errores
 */
int main() {
    // ERROR 4: Vector vacío causará división por cero
    vector<int> listaVacia;
    double promedio = calcularPromedio(listaVacia);
    cout << "Promedio: " << promedio << endl;
    
    // ERROR 5: Llamar función con parámetro que causará problemas
    mostrarTablaMultiplicar(0);  // Multiplicar por 0 no es útil
    
    // ERROR 6: Función que accede fuera de límites
    procesarArray();
    
    return 0;
}