<?php
// 1. Baca file TSV
$filename = "katalog_gempa_v2.tsv";
$data = [];

if (($handle = fopen($filename, "r")) !== false) {
    while (($row = fgetcsv($handle, 1000, "\t")) !== false) {
        $data[] = $row;
    }
    fclose($handle);
}

// 2. Ekstrak fitur: latitude, longitude, magnitude, depth
$fitur = [];
foreach ($data as $index => $row) {
    if ($index == 0) continue; // Skip header
    if (count($row) < 7) continue; // Pastikan data cukup kolom

    $lat = (float) $row[2];
    $lon = (float) $row[3];
    $mag = (float) $row[4];
    $dep = (float) $row[6];

    $fitur[] = [$lat, $lon, $mag, $dep];
}

// 3. Implementasi K-Means
function kmeans($data, $k = 3, $maxIterations = 100) {
    $centroids = array_slice($data, 0, $k);
    $clusters = [];

    for ($i = 0; $i < $maxIterations; $i++) {
        $clusters = array_fill(0, $k, []);

        foreach ($data as $point) {
            $minDist = INF;
            $closest = 0;

            foreach ($centroids as $idx => $centroid) {
                $dist = 0;
                foreach ($point as $j => $val) {
                    $dist += pow($val - $centroid[$j], 2);
                }
                $dist = sqrt($dist);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $closest = $idx;
                }
            }

            $clusters[$closest][] = $point;
        }

        $newCentroids = [];
        foreach ($clusters as $cluster) {
            $n = count($cluster);
            if ($n == 0) continue;
            $sum = array_fill(0, count($cluster[0]), 0);
            foreach ($cluster as $point) {
                foreach ($point as $j => $val) {
                    $sum[$j] += $val;
                }
            }
            $newCentroids[] = array_map(fn($x) => $x / $n, $sum);
        }

        if ($centroids == $newCentroids) break;
        $centroids = $newCentroids;
    }

    return [$centroids, $clusters];
}

// 4. Jalankan K-Means
list($centroids, $clusters) = kmeans($fitur, 3);

// 5. Tampilkan Hasil
echo "<h2>Hasil Clustering Gempa (K = 3)</h2>";
foreach ($clusters as $i => $cluster) {
    echo "<h3>Cluster " . ($i + 1) . "</h3>";
    echo "<table border='1' cellpadding='5'><tr><th>Latitude</th><th>Longitude</th><th>Magnitude</th><th>Depth</th></tr>";
    foreach (array_slice($cluster, 0, 10) as $point) { // Tampilkan 10 data per cluster
        echo "<tr>";
        foreach ($point as $val) {
            echo "<td>" . round($val, 3) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table><br>";
}
?>
