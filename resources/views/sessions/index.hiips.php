<table>
    <thead>
        <tr>
            <th>Jour</th>
            <th>Horaire</th>
            <th>Cours</th>
            <th>Enseignant</th>
            <th>Salle</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sessions as $s): ?>
            <tr>
                <td><?= $this->clean($s['day_of_week']) ?></td>
                <td><?= $this->clean($s['start_time']) ?> - <?= $this->clean($s['end_time']) ?></td>
                <td><?= $this->clean($s['course_name']) ?></td>
                <td>M. <?= $this->clean($s['teacher_name']) ?></td>
                <td><?= $this->clean($s['classroom_name']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>