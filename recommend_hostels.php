<?php
function recommendHostels($conn, $user_id, $searchedLocation = null) {
   
    $stmt = $conn->prepare("
        SELECT 
            (SELECT COUNT(*) FROM hostel_reviews WHERE user_id = ?) + 
            (SELECT COUNT(*) FROM bookings WHERE user_id = ?) AS activity_count
    ");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();

    $activityCount = 0;
    $stmt->bind_result($activityCount);
    $stmt->fetch();
    $stmt->close();

    if ($activityCount > 0) {
        $hostels = collaborativeFiltering($conn, $user_id);
        if (!empty($hostels)) {
            return [
                'hostels' => $hostels,
                'source' => 'collaborative'
            ];
        }
    }

  
    if ($searchedLocation) {
        $location = '%' . $searchedLocation . '%';
    } else {
        $stmt = $conn->prepare("SELECT location FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $location = '';
        $stmt->bind_result($location);
        $stmt->fetch();
        $stmt->close();
        $location = '%' . $location . '%';
    }

    $stmt = $conn->prepare("
        SELECT h.*, AVG(hr.rating) AS rating
        FROM hostels h
        LEFT JOIN hostel_reviews hr ON h.id = hr.hostel_id
        WHERE h.location LIKE ?
        GROUP BY h.id
        ORDER BY rating DESC
        LIMIT 4
    ");
    $stmt->bind_param("s", $location);
    $stmt->execute();

    $result = $stmt->get_result();
    $hostels = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return [
        'hostels' => $hostels,
        'source' => $searchedLocation ? 'search' : 'location'
    ];
}

function collaborativeFiltering($conn, $user_id) {
    $ratings = [];
    $result = $conn->query("SELECT user_id, hostel_id, rating FROM hostel_reviews");
    while ($row = $result->fetch_assoc()) {
        $ratings[$row['user_id']][$row['hostel_id']] = $row['rating'];
    }

    if (!isset($ratings[$user_id]) || empty($ratings[$user_id])) {
        return [];
    }

    $targetRatings = $ratings[$user_id];

    $similarities = [];
    foreach ($ratings as $other_user => $otherRatings) {
        if ($other_user == $user_id) continue;

        $common = array_intersect_key($targetRatings, $otherRatings);
        if (count($common) == 0) continue;

        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        foreach ($common as $hostel_id => $rating) {
            $rA = $targetRatings[$hostel_id];
            $rB = $otherRatings[$hostel_id];
            $dotProduct += $rA * $rB;
            $normA += $rA ** 2;
            $normB += $rB ** 2;
        }

        if ($normA > 0 && $normB > 0) {
            $similarities[$other_user] = $dotProduct / (sqrt($normA) * sqrt($normB));
        }
    }

    $scores = [];
    foreach ($ratings as $other_user => $otherRatings) {
        if (!isset($similarities[$other_user])) continue;

        $similarity = $similarities[$other_user];
        foreach ($otherRatings as $hostel_id => $rating) {
            if (isset($targetRatings[$hostel_id])) continue;

            if (!isset($scores[$hostel_id])) {
                $scores[$hostel_id] = ['score' => 0, 'simTotal' => 0];
            }

            $scores[$hostel_id]['score'] += $similarity * $rating;
            $scores[$hostel_id]['simTotal'] += abs($similarity);
        }
    }

    $predictions = [];
    foreach ($scores as $hostel_id => $data) {
        if ($data['simTotal'] > 0) {
            $predictions[$hostel_id] = $data['score'] / $data['simTotal'];
        }
    }

    if (empty($predictions)) {
        return [];
    }

    arsort($predictions); 

    $hostels = [];
    $hostel_ids = array_keys($predictions);
    $ids_string = implode(",", array_map('intval', array_slice($hostel_ids, 0, 4)));

    $query = "SELECT * FROM hostels WHERE id IN ($ids_string)";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $hostels[] = $row;
    }

    return $hostels;
}
?>
