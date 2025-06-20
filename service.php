<?php
// service.php
require_once 'db_connect.php';

/**
 * Reorder today's queue according to triage levels:
 *  Emergency → High → Normal, then by original position
 */
function reorderQueue() {
    global $conn;
    $today = date('Y-m-d');

    // fetch queue entries for today that are not paused
    $sql = "
      SELECT q.queue_id,
             q.position,
             COALESCE(
               (SELECT triage_level 
                  FROM Triage t 
                 WHERE t.patient_id = q.patient_id 
                   AND DATE(t.time)= '$today'
                 ORDER BY t.time DESC LIMIT 1),
               'Normal'
             ) AS triage_level
        FROM Queue q
        JOIN Appointment a ON q.appointment_id = a.appointment_id
       WHERE a.scheduled_date = '$today'
         AND q.status = 'Waiting'
         AND q.paused = FALSE
    ";
    $res = $conn->query($sql);
    $items = [];
    while ($row = $res->fetch_assoc()) {
      // priority rank
      $prio = $row['triage_level']==='Emergency' ? 1
            : ($row['triage_level']==='High'      ? 2 : 3);
      $items[] = [
        'id'       => (int)$row['queue_id'],
        'prio'     => $prio,
        'old_pos'  => (int)$row['position']
      ];
    }

    // sort by triage, then by old position
    usort($items, function($a, $b){
      if ($a['prio'] !== $b['prio']) {
        return $a['prio'] - $b['prio'];
      }
      return $a['old_pos'] - $b['old_pos'];
    });

    // write back new positions
    foreach ($items as $i => $it) {
      $newPos = $i + 1;
      $conn->query("UPDATE Queue 
                      SET position = $newPos 
                    WHERE queue_id  = {$it['id']}");
    }
}

/**
 * Estimate wait time in minutes (20min per position)
 */
function estimateWaitTime($pos) {
    return $pos * 20;
}
