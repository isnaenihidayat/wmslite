<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $now  = Carbon::now();
        $user = DB::table('el_user')->first();
        $uid  = $user?->user_id ?? 1;

        // ── Product Categories ─────────────────────────────────────────
        $existCat = DB::table('el_product_category')->count();
        if ($existCat === 0) {
            DB::table('el_product_category')->insert([
                ['id' => 1, 'name' => 'Electronics',   'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 2, 'name' => 'Spare Parts',   'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 3, 'name' => 'General Cargo', 'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 4, 'name' => 'Chemical',      'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 5, 'name' => 'F&B',           'created_by' => $uid, 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // ── Inbound Headers ────────────────────────────────────────────
        $inboundData = [
            [
                'hawb'               => 'HAWB-2024-001',
                'descr'              => 'HP Laptop Components Batch A',
                'product_category_id'=> 1,
                'modality'           => 'Air',
                'delivery_id'        => null,
                'qty'                => '50',
                'po'                 => 'PO-2024-001',
                'locator'            => 'arcadia',
                'checker'            => 'Admin WMS',
                'status'             => 'successful',
                'from_shipment'      => 0,
                'etd'                => '2024-01-10',
                'eta'                => '2024-01-15',
                'ata'                => '2024-01-15',
                'created_by'         => $uid,
                'date_created'       => $now->copy()->subDays(60),
                'date_updated'       => $now->copy()->subDays(55),
            ],
            [
                'hawb'               => 'HAWB-2024-002',
                'descr'              => 'Server Racks Dell R750',
                'product_category_id'=> 2,
                'modality'           => 'Sea',
                'delivery_id'        => null,
                'qty'                => '10',
                'po'                 => 'PO-2024-002',
                'locator'            => 'cengkareng',
                'checker'            => 'Admin WMS',
                'status'             => 'inprogress',
                'from_shipment'      => 0,
                'etd'                => '2024-02-01',
                'eta'                => '2024-02-20',
                'ata'                => null,
                'created_by'         => $uid,
                'date_created'       => $now->copy()->subDays(30),
                'date_updated'       => $now->copy()->subDays(28),
            ],
            [
                'hawb'               => 'HAWB-2024-003',
                'descr'              => 'Industrial Motors & Pumps',
                'product_category_id'=> 2,
                'modality'           => 'Land',
                'delivery_id'        => null,
                'qty'                => '25',
                'po'                 => 'PO-2024-003',
                'locator'            => 'surabaya',
                'checker'            => 'Admin WMS',
                'status'             => 'created',
                'from_shipment'      => 0,
                'etd'                => $now->copy()->addDays(5)->format('Y-m-d'),
                'eta'                => $now->copy()->addDays(15)->format('Y-m-d'),
                'ata'                => null,
                'created_by'         => $uid,
                'date_created'       => $now->copy()->subDays(5),
                'date_updated'       => $now->copy()->subDays(5),
            ],
            [
                'hawb'               => 'HAWB-2024-004',
                'descr'              => 'Chemical Raw Material Batch C',
                'product_category_id'=> 4,
                'modality'           => 'Air',
                'delivery_id'        => null,
                'qty'                => '100',
                'po'                 => 'PO-2024-004',
                'locator'            => 'arcadia',
                'checker'            => 'Admin WMS',
                'status'             => 'failed',
                'from_shipment'      => 0,
                'etd'                => '2024-03-01',
                'eta'                => '2024-03-10',
                'ata'                => '2024-03-12',
                'created_by'         => $uid,
                'date_created'       => $now->copy()->subDays(20),
                'date_updated'       => $now->copy()->subDays(18),
            ],
        ];

        foreach ($inboundData as $row) {
            $exists = DB::table('el_inbound_header')->where('hawb', $row['hawb'])->exists();
            if (!$exists) {
                DB::table('el_inbound_header')->insert($row);
            }
        }

        // ── Shipments (from_shipment = 1) ──────────────────────────────
        $shipmentData = [
            [
                'hawb'               => 'SHP-2024-001',
                'descr'              => 'Import Shipment - Electronics Q1',
                'product_category_id'=> 1,
                'modality'           => 'Air',
                'delivery_id'        => null,
                'qty'                => '200',
                'po'                 => 'PO-SHP-001',
                'locator'            => 'arcadia',
                'checker'            => 'Admin WMS',
                'status'             => 'successful',
                'from_shipment'      => 1,
                'etd'                => '2024-01-05',
                'eta'                => '2024-01-12',
                'ata'                => '2024-01-12',
                'created_by'         => $uid,
                'date_created'       => $now->copy()->subDays(65),
                'date_updated'       => $now->copy()->subDays(60),
            ],
            [
                'hawb'               => 'SHP-2024-002',
                'descr'              => 'Automotive Parts Shipment',
                'product_category_id'=> 2,
                'modality'           => 'Sea',
                'delivery_id'        => null,
                'qty'                => '500',
                'po'                 => 'PO-SHP-002',
                'locator'            => 'cengkareng',
                'checker'            => 'Admin WMS',
                'status'             => 'inprogress',
                'from_shipment'      => 1,
                'etd'                => $now->copy()->subDays(10)->format('Y-m-d'),
                'eta'                => $now->copy()->addDays(5)->format('Y-m-d'),
                'ata'                => null,
                'created_by'         => $uid,
                'date_created'       => $now->copy()->subDays(15),
                'date_updated'       => $now->copy()->subDays(12),
            ],
            [
                'hawb'               => 'SHP-2024-003',
                'descr'              => 'F&B Import Indonesia',
                'product_category_id'=> 5,
                'modality'           => 'Air',
                'delivery_id'        => null,
                'qty'                => '150',
                'po'                 => 'PO-SHP-003',
                'locator'            => 'default',
                'checker'            => 'Admin WMS',
                'status'             => 'created',
                'from_shipment'      => 1,
                'etd'                => $now->copy()->addDays(3)->format('Y-m-d'),
                'eta'                => $now->copy()->addDays(10)->format('Y-m-d'),
                'ata'                => null,
                'created_by'         => $uid,
                'date_created'       => $now,
                'date_updated'       => $now,
            ],
        ];

        foreach ($shipmentData as $row) {
            $exists = DB::table('el_inbound_header')->where('hawb', $row['hawb'])->exists();
            if (!$exists) {
                DB::table('el_inbound_header')->insert($row);
            }
        }

        // ── Inbound Details ────────────────────────────────────────────
        $detailData = [
            ['hawb' => 'HAWB-2024-001', 'descr' => 'HP EliteBook 840',  'loc' => 'A-01-01', 'weight' => 20.00, 'flag' => 1, 'scan_time' => $now->copy()->subDays(55)->format('Y-m-d H:i:s')],
            ['hawb' => 'HAWB-2024-001', 'descr' => 'HP Pavilion 15',    'loc' => 'A-01-02', 'weight' => 30.00, 'flag' => 1, 'scan_time' => $now->copy()->subDays(55)->format('Y-m-d H:i:s')],
            ['hawb' => 'HAWB-2024-002', 'descr' => 'Dell R750 Server',  'loc' => 'B-02-01', 'weight' => 45.00, 'flag' => 0, 'scan_time' => null],
            ['hawb' => 'HAWB-2024-002', 'descr' => 'Dell Rails Kit',    'loc' => 'B-02-02', 'weight' => 5.00,  'flag' => 0, 'scan_time' => null],
        ];

        foreach ($detailData as $detail) {
            $exists = DB::table('el_inbound_details')
                ->where('hawb', $detail['hawb'])
                ->where('descr', $detail['descr'])
                ->exists();
            if (!$exists) {
                DB::table('el_inbound_details')->insert($detail);
            }
        }

        // ── Outbound Headers ───────────────────────────────────────────
        $outboundData = [
            [
                'po'          => 'GON-2024-001',
                'destination' => 'PT Maju Bersama - Jakarta',
                'qty'         => '20',
                'checker'     => 'Admin WMS',
                'status'      => 'successful',
                'transporter' => 'JNE',
                'created_by'  => $uid,
                'date_created'=> $now->copy()->subDays(50),
                'date_updated'=> $now->copy()->subDays(45),
            ],
            [
                'po'          => 'GON-2024-002',
                'destination' => 'PT Teknologi Nusantara - Surabaya',
                'qty'         => '5',
                'checker'     => 'Admin WMS',
                'status'      => 'inprogress',
                'transporter' => 'TIKI',
                'created_by'  => $uid,
                'date_created'=> $now->copy()->subDays(10),
                'date_updated'=> $now->copy()->subDays(8),
            ],
            [
                'po'          => 'GON-2024-003',
                'destination' => 'PT Sentosa Makmur - Bandung',
                'qty'         => '15',
                'checker'     => 'Admin WMS',
                'status'      => 'created',
                'transporter' => 'SiCepat',
                'created_by'  => $uid,
                'date_created'=> $now->copy()->subDays(2),
                'date_updated'=> $now->copy()->subDays(2),
            ],
        ];

        foreach ($outboundData as $row) {
            $exists = DB::table('el_outbound_header')->where('po', $row['po'])->exists();
            if (!$exists) {
                DB::table('el_outbound_header')->insert($row);
            }
        }

        $this->command->info('✅ Demo data seeded successfully!');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Inbound',    DB::table('el_inbound_header')->where('from_shipment', 0)->count()],
                ['Shipments',  DB::table('el_inbound_header')->where('from_shipment', 1)->count()],
                ['Outbound',   DB::table('el_outbound_header')->count()],
                ['Categories', DB::table('el_product_category')->count()],
            ]
        );
    }
}
