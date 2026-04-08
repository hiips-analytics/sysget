<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>
<div class="schedule-wrapper">
    <table class="schedule-table">
        <thead>
            <tr>
                <th>Heure</th>
                <?php foreach ($days as $day): ?>
                    <th><?= $this->clean($day) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($timeSlots as $time): ?>
                <tr>
                    <td class="time-cell"><?= $this->clean($time) ?></td>
                    <?php foreach ($days as $day): ?>
                        <td class="schedule-cell">
                            <?php if (!empty($schedule[$day][$time])): ?>
                                <?php $session = $schedule[$day][$time]; ?>
                                <div class="session-chip"><?= $this->clean($session['course_name']) ?></div>
                                <div class="session-meta">Prof. <?= $this->clean($session['teacher_name']) ?></div>
                                <div class="session-meta">Salle <?= $this->clean($session['classroom_name']) ?></div>
                                <div class="session-meta"><?= $this->clean($session['start_time']) ?> - <?= $this->clean($session['end_time']) ?></div>
                            <?php else: ?>
                                <span class="empty-cell">-</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
