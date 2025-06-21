<?php
require_once 'db_connect.php';

function reorderQueue() {
    global $conn;
    $today = date('Y-m-d');

    $sql = "
      SELECT 
        q.queue_id,
        q.position AS old_pos,
        a.scheduled_time,
        COALESCE(
          (SELECT triage_level 
             FROM Triage t 
            WHERE t.patient_id = q.patient_id 
              AND DATE(t.time)= '$today'
            ORDER BY t.time DESC LIMIT 1),
          'Normal'
        ) AS triage_level,
        q.appointment_id
      FROM Queue q
      JOIN Appointment a ON q.appointment_id = a.appointment_id
      WHERE a.scheduled_date = '$today'
        AND q.status = 'Waiting'
    ";
    $res = $conn->query($sql);
    if (!$res) return;

    $items    = [];
    $times    = []; 
    while ($row = $res->fetch_assoc()) {
      $prio = ($row['triage_level']==='Emergency' ? 1
             : ($row['triage_level']==='High'      ? 2 : 3));
      $items[] = [
        'queue_id'      => (int)$row['queue_id'],
        'old_pos'       => (int)$row['old_pos'],
        'prio'          => $prio,
        'appointment_id'=> (int)$row['appointment_id'],
      ];
      $times[(int)$row['old_pos']] = $row['scheduled_time'];
    }

    usort($items, function($a, $b){
      if ($a['prio'] !== $b['prio']) {
        return $a['prio'] - $b['prio'];
      }
      return $a['old_pos'] - $b['old_pos'];
    });

    ksort($times);
    $slots = array_values($times); 

    foreach ($items as $index => $it) {
      $newPos   = $index + 1;
      $newTime  = $slots[$index] ?? $slots[count($slots)-1];
      $qid      = $it['queue_id'];
      $aid      = $it['appointment_id'];

      $conn->query("
        UPDATE Queue
           SET position = $newPos
         WHERE queue_id = $qid
      ");

      $conn->query("
        UPDATE Appointment
           SET scheduled_time = '$newTime'
         WHERE appointment_id = $aid
      ");
    }
}

function estimateWaitTime($pos) {
    return $pos * 20;
}
