-- Désactivation des vérifications pour pouvoir vider les tables sans erreur
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM sessions; 
DELETE FROM time_slots; 
DELETE FROM courses; 
DELETE FROM users; 
DELETE FROM fields; 
DELETE FROM classrooms;

-- Réactivation des vérifications
SET FOREIGN_KEY_CHECKS = 1;

-- 1. FILIÈRES
INSERT INTO fields (id, name) VALUES (1, 'Génie Logiciel'), (2, 'Sécurité Informatique');

-- 2. UTILISATEURS
INSERT INTO users (id, name, email, password, role, field_id) VALUES 
(1, 'Admin Sysget', 'admin@sysget.sp', 'pass', 'admin', NULL),
(2, 'Dr. Fossuo', 'fossuo@sysget.sp', 'pass', 'teacher', NULL),
(3, 'Mme. Bella', 'bella@sysget.sp', 'pass', 'teacher', NULL),
(4, 'M. Njikam', 'njikam@sysget.sp', 'pass', 'teacher', NULL),
(5, 'M. Talla', 'talla@sysget.sp', 'pass', 'teacher', NULL),
(6, 'Benoit', 'benoit@sysget.sp', 'pass', 'student', 1),
(7, 'Alice', 'alice@sysget.sp', 'pass', 'student', 2);

-- 3. SALLES
INSERT INTO classrooms (id, name, capacity) VALUES 
(1, 'Amphi 500', 500), (2, 'Salle 101', 40), (3, 'Labo Info 1', 30), (4, 'Salle 204', 50);

-- 4. COURS
INSERT INTO courses (id, name, teacher_id) VALUES 
(1, 'Architecture MVC', 2), (2, 'Bases de Données NoSQL', 2),
(3, 'Algorithmique Avancée', 3), (4, 'Réseaux Mobiles', 3),
(5, 'Sécurité Réseaux', 4), (6, 'Cryptographie', 4),
(7, 'Anglais Technique', 5), (8, 'Gestion de Projets', 5);

-- 5. DISPONIBILITÉS (TimeSlots)
INSERT INTO time_slots (teacher_id, day_of_week, start_time, end_time) VALUES 
(2, 'Lundi', '08:00:00', '14:00:00'), (2, 'Mardi', '13:00:00', '17:00:00'),
(3, 'Lundi', '10:00:00', '16:00:00'), (3, 'Mercredi', '08:00:00', '12:00:00'),
(4, 'Jeudi', '08:00:00', '18:00:00'), (5, 'Vendredi', '08:00:00', '12:00:00');

-- 6. SESSIONS (Emploi du temps)
INSERT INTO sessions (course_id, classroom_id, day_of_week, start_time, end_time) VALUES 
(1, 1, 'Lundi', '08:00:00', '10:00:00'),
(2, 3, 'Lundi', '11:00:00', '13:00:00'),
(3, 2, 'Lundi', '14:00:00', '16:00:00'),
(4, 4, 'Mercredi', '08:00:00', '11:00:00'),
(5, 1, 'Jeudi', '09:00:00', '12:00:00'),
(6, 3, 'Jeudi', '14:00:00', '17:00:00'),
(7, 2, 'Vendredi', '08:30:00', '10:30:00');