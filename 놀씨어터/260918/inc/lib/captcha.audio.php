<?php
declare(strict_types=1);

session_start();
ini_set('display_errors', '0');

require __DIR__ . '/base.class.php';

$audioDir = ROOT . '/resource/audio/captcha';

// 세션에 저장한 문구 가져오기 (이미지 생성 시 저장했던 값)
$phrase = $_SESSION['captcha_phrase'] ?? '';
if ($phrase === '' || !preg_match('/^\d+$/', $phrase)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'invalid phrase';
    exit;
}

// 숫자 -> 파일 경로 매핑
$files = [];
for ($i = 0, $n = strlen($phrase); $i < $n; $i++) {
    $digit = $phrase[$i];
    $path  = $audioDir . '/' . $digit . '.wav';
    if (!is_file($path)) {
        error_log("captcha.audio: missing wav for digit {$digit}");
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'audio missing';
        exit;
    }
    $files[] = $path;
}

// WAV 병합 (헤더 재계산)
try {
    $wav = concatPcmWav($files); // 아래 함수
} catch (Throwable $e) {
    error_log('captcha.audio concat error: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'audio error';
    exit;
}

if (ob_get_length()) { ob_clean(); }
header_remove('X-Powered-By');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: audio/wav');
header('Content-Length: ' . strlen($wav));
echo $wav;
exit;

/**
 * 여러 PCM WAV(동일 포맷) 파일을 하나의 WAV로 결합
 * 지원: PCM 16-bit mono/same sample rate (권장 16000 Hz)
 */
function concatPcmWav(array $paths): string {
    $dataChunks = [];
    $fmtChunk   = null;
    $sampleRate = null;
    $bitsPer    = null;
    $channels   = null;

    foreach ($paths as $p) {
        [$fmt, $data] = parseWav($p);

        // 첫 파일의 포맷을 기준으로 고정
        if ($fmtChunk === null) {
            if ($fmt['audioFormat'] !== 1) { // PCM only
                throw new RuntimeException('Only PCM WAV is supported');
            }
            $fmtChunk   = $fmt;
            $sampleRate = $fmt['sampleRate'];
            $bitsPer    = $fmt['bitsPerSample'];
            $channels   = $fmt['numChannels'];
        } else {
            // 포맷 다르면 에러
            if (
                $fmt['audioFormat']  !== 1 ||
                $fmt['sampleRate']   !== $sampleRate ||
                $fmt['bitsPerSample']!== $bitsPer ||
                $fmt['numChannels']  !== $channels
            ) {
                throw new RuntimeException('Inconsistent WAV format among files');
            }
        }
        $dataChunks[] = $data;
    }

    $allData = implode('', $dataChunks);

    // WAV 헤더 작성
    $byteRate   = $sampleRate * $channels * ($bitsPer / 8);
    $blockAlign = $channels * ($bitsPer / 8);
    $subchunk2Size = strlen($allData);
    $chunkSize = 36 + $subchunk2Size;

    $header  = '';
    $header .= "RIFF";
    $header .= pack('V', $chunkSize);
    $header .= "WAVE";

    // fmt chunk
    $header .= "fmt ";
    $header .= pack('V', 16); // PCM fmt chunk size
    $header .= pack('v', 1);  // audioFormat = 1 (PCM)
    $header .= pack('v', $channels);
    $header .= pack('V', $sampleRate);
    $header .= pack('V', $byteRate);
    $header .= pack('v', $blockAlign);
    $header .= pack('v', $bitsPer);

    // data chunk
    $header .= "data";
    $header .= pack('V', $subchunk2Size);

    return $header . $allData;
}

/**
 * 간단한 WAV 파서 (RIFF/WAVE/PCM 전제)
 * @return array{0: array fmt, 1: string dataChunk}
 */
function parseWav(string $path): array {
    $bin = file_get_contents($path);
    if ($bin === false || strlen($bin) < 44) {
        throw new RuntimeException('Invalid WAV file: ' . $path);
    }
    $pos = 0;

    $riff = substr($bin, $pos, 4); $pos += 4;
    if ($riff !== 'RIFF') throw new RuntimeException('Not RIFF');
    $pos += 4; // chunkSize
    $wave = substr($bin, $pos, 4); $pos += 4;
    if ($wave !== 'WAVE') throw new RuntimeException('Not WAVE');

    $fmt = null;
    $data = null;

    while ($pos + 8 <= strlen($bin)) {
        $chunkId = substr($bin, $pos, 4); $pos += 4;
        $chunkSz = unpack('V', substr($bin, $pos, 4))[1]; $pos += 4;

        if ($chunkId === 'fmt ') {
            $audioFormat  = unpack('v', substr($bin, $pos, 2))[1];
            $numChannels  = unpack('v', substr($bin, $pos+2, 2))[1];
            $sampleRate   = unpack('V', substr($bin, $pos+4, 4))[1];
            $byteRate     = unpack('V', substr($bin, $pos+8, 4))[1];
            $blockAlign   = unpack('v', substr($bin, $pos+12, 2))[1];
            $bitsPerSample= unpack('v', substr($bin, $pos+14, 2))[1];
            $fmt = compact('audioFormat','numChannels','sampleRate','byteRate','blockAlign','bitsPerSample');
        } elseif ($chunkId === 'data') {
            $data = substr($bin, $pos, $chunkSz);
        }

        $pos += $chunkSz + ($chunkSz % 2); // padding
        if ($fmt !== null && $data !== null) break;
    }
    if ($fmt === null || $data === null) {
        throw new RuntimeException('fmt/data chunk not found: ' . $path);
    }
    return [$fmt, $data];
}
