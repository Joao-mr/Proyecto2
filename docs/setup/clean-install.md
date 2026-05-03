# Clean Install

## Seed layers

1. Foundation seed data
   - roles
   - permissions
   - admin user
   - one normal user
   - categories
   - image catalog
2. Optional demo seed data
   - salas
   - partidas
   - usuario_partida
   - usuario_sala
   - partida_imagen
3. Runtime-generated data
   - real gameplay history
   - recalculated user stats

A clean installation from `main` must finish with user stat columns initialized to zero unless optional demo gameplay seeders are executed explicitly.

Bootstrap demo user contract:

`admin@demo.com / 12345678`  
`user@demo.com / 12345678`

Do not keep personal or historical local users such as private email addresses in the clean seed path.

`database/sql/clean-bootstrap.sql` is a support export generated from the final clean migrated-and-seeded state. The official installation path remains `php artisan migrate --seed`.
