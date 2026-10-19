# Bangun data/geo-id-v4.bin & geo-id-v6.bin + geo-id-places.json dari DB-IP City Lite (hanya ID).
import csv, ipaddress, json, struct, sys
src, out = sys.argv[1], sys.argv[2]
PROV = {"Jakarta":"DKI Jakarta","West Java":"Jawa Barat","East Java":"Jawa Timur","Central Java":"Jawa Tengah",
"Banten":"Banten","Riau":"Riau","Bali":"Bali","South Sulawesi":"Sulawesi Selatan","North Sumatra":"Sumatera Utara",
"Yogyakarta":"DI Yogyakarta","South Sumatra":"Sumatera Selatan","Lampung":"Lampung","South Kalimantan":"Kalimantan Selatan",
"East Kalimantan":"Kalimantan Timur","West Nusa Tenggara":"Nusa Tenggara Barat","West Kalimantan":"Kalimantan Barat",
"West Sumatra":"Sumatera Barat","North Sulawesi":"Sulawesi Utara","Aceh":"Aceh","Riau Islands":"Kepulauan Riau",
"East Nusa Tenggara":"Nusa Tenggara Timur","Southeast Sulawesi":"Sulawesi Tenggara","Jambi":"Jambi",
"Central Kalimantan":"Kalimantan Tengah","Gorontalo":"Gorontalo","Central Sulawesi":"Sulawesi Tengah","Papua":"Papua",
"Bangka–Belitung Islands":"Kepulauan Bangka Belitung","West Sulawesi":"Sulawesi Barat","Maluku":"Maluku",
"North Kalimantan":"Kalimantan Utara","Bengkulu":"Bengkulu","North Maluku":"Maluku Utara","Central Papua":"Papua Tengah",
"Southwest Papua":"Papua Barat Daya","West Papua":"Papua Barat","South Papua":"Papua Selatan","Highland Papua":"Papua Pegunungan"}
places, idx, rows = [], {}, {4: [], 6: []}
for r in csv.reader(open(src, encoding="utf-8")):
    a, b = ipaddress.ip_address(r[0]), ipaddress.ip_address(r[1])
    prov = PROV.get(r[4], r[4] or "Tidak diketahui")
    key = (r[5] or prov, prov)
    if key not in idx:
        idx[key] = len(places); places.append(list(key))
    rows[a.version].append((int(a), int(b), idx[key]))
assert len(places) < 65536
for v, size in ((4, 4), (6, 16)):
    rs = sorted(rows[v]); merged = []
    for s, e, p in rs:
        if merged and merged[-1][2] == p and merged[-1][1] + 1 == s:
            merged[-1][1] = e
        else:
            merged.append([s, e, p])
    with open(f"{out}/geo-id-v{v}.bin", "wb") as f:
        for s, e, p in merged:
            f.write(s.to_bytes(size, "big") + e.to_bytes(size, "big") + struct.pack(">H", p))
    print(v, len(rs), "->", len(merged))
json.dump(places, open(f"{out}/geo-id-places.json", "w"), ensure_ascii=False, separators=(",", ":"))
print("places", len(places))
