#!/bin/bash

BASE="https://smart-slot.test/api"
DATE="06-05-2026"

echo ""
echo "=== 1. Login Alice ==="
TOKEN_ALICE=$(curl -s -X POST "$BASE/auth/token" \
  -H "Content-Type: application/json" \
  -d '{"email":"alice@test.com","password":"password","device_name":"demo"}' | jq -r '.token')
echo "Token Alice: $TOKEN_ALICE"

echo ""
echo "=== 2. Alice submits a charging request → available slot, directly assigned ==="
ALICE_REQUEST=$(curl -s -X POST "$BASE/charging-requests" \
  -H "Authorization: Bearer $TOKEN_ALICE" \
  -H "Content-Type: application/json" \
  -d "{
    \"battery_percentage\": 42,
    \"charging_window\": {
      \"start_time\": \"$DATE 10:00\",
      \"end_time\": \"$DATE 12:00\"
    }
  }" | jq -r '.data.id')
echo "Request Alice ID: $ALICE_REQUEST"

echo ""
echo "=== 3. Login Bob ==="
TOKEN_BOB=$(curl -s -X POST "$BASE/auth/token" \
  -H "Content-Type: application/json" \
  -d '{"email":"bob@test.com","password":"password","device_name":"demo"}' | jq -r '.token')
echo "Token Bob: $TOKEN_BOB"

echo ""
echo "=== 4. Bob also submits → assigned on another slot ==="
curl -s -X POST "$BASE/charging-requests" \
  -H "Authorization: Bearer $TOKEN_BOB" \
  -H "Content-Type: application/json" \
  -d "{
    \"battery_percentage\": 15,
    \"charging_window\": {
      \"start_time\": \"$DATE 10:00\",
      \"end_time\": \"$DATE 12:00\"
    }
  }" | jq

echo ""
echo "=== 5. Alice attempts on a 2nd request → rejected (running session) ==="
curl -s -X POST "$BASE/charging-requests" \
  -H "Authorization: Bearer $TOKEN_ALICE" \
  -H "Content-Type: application/json" \
  -d "{
    \"battery_percentage\": 42,
    \"charging_window\": {
      \"start_time\": \"$DATE 14:00\",
      \"end_time\": \"$DATE 16:00\"
    }
  }" | jq

echo ""
echo "=== 6. Alice ends her session → freed slot ==="
curl -s -X POST "$BASE/charging-requests/$ALICE_REQUEST/end" \
  -H "Authorization: Bearer $TOKEN_ALICE" | jq
