@echo off
REM Script para ejecutar el respaldo automático
REM Este archivo puede ser usado en el Programador de Tareas de Windows

cd /d "C:\xampp\htdocs\Sistema_Escuela\Respaldo"
C:\xampp\php\php.exe respaldo_automatico.php

REM Pausar solo si se ejecuta manualmente (no desde el programador de tareas)
if "%1" NEQ "auto" pause