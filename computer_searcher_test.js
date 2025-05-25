"use strict"

const processors = [

    // AMD

    {
        name: "AMD Ryzen 3 4100",
        manufacturer: "AMD",
        series: "Ryzen 3",
        cores: 4,
        threads: 8,
        base_clock: 3.8, // GHz
        max_boost_clock: 4, // GHz
        l3_cache: 4, // MB
        integrated_gpu: false,
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 64.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 3 4300G",
        manufacturer: "AMD",
        series: "Ryzen 3",
        cores: 4,
        threads: 8,
        base_clock: 3.8, // GHz
        max_boost_clock: 4, // GHz
        l3_cache: 4, // MB
        integrated_gpu: "AMD Radeon Graphics 1700 MHz",
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 88.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 4500",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 3.6, // GHz
        max_boost_clock: 4.1, // GHz
        l3_cache: 8, // MB
        integrated_gpu: false,
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 68.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 3600",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 3.6, // GHz
        max_boost_clock: 4.2, // GHz
        l3_cache: 32, // MB
        integrated_gpu: false,
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 76.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 5500",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 3.6, // GHz
        max_boost_clock: 4.2, // GHz
        l3_cache: 16, // MB
        integrated_gpu: false,
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 86.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 5500GT",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 3.6, // GHz
        max_boost_clock: 4.4, // GHz
        l3_cache: 16, // MB
        integrated_gpu: "AMD Radeon Graphics",
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 117.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 5600",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 3.5, // GHz
        max_boost_clock: 4.4, // GHz
        l3_cache: 32, // MB
        integrated_gpu: false,
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 124.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 5600GT",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 3.6, // GHz
        max_boost_clock: 4.6, // GHz
        l3_cache: 16, // MB
        integrated_gpu: "AMD Radeon Graphics",
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 136.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 5600G",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 3.9, // GHz
        max_boost_clock: 4.4, // GHz
        l3_cache: 16, // MB
        integrated_gpu: "AMD Radeon Graphics 1 900 MHz",
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 136.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 8500G",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 3.4, // GHz
        max_boost_clock: 5, // GHz
        l3_cache: 16, // MB
        integrated_gpu: "AMD Radeon 740M 2 200 MHz",
        tdp: 65, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 151.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 5600T",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 3.5, // GHz
        max_boost_clock: 4.5, // GHz
        l3_cache: 32, // MB
        integrated_gpu: false,
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 160.82 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 5600XT",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 3.7, // GHz
        max_boost_clock: 4.7, // GHz
        l3_cache: 32, // MB
        integrated_gpu: false,
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 193.63 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 7600",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 3.8, // GHz
        max_boost_clock: 5.1, // GHz
        l3_cache: 32, // MB
        integrated_gpu: "AMD Radeon Graphics",
        tdp: 65, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 209.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 7600X",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 4.7, // GHz
        max_boost_clock: 5.3, // GHz
        l3_cache: 32, // MB
        integrated_gpu: "AMD Radeon Graphics 2 200 MHz",
        tdp: 105, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 229.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 9600X",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 3.9, // GHz
        max_boost_clock: 5.4, // GHz
        l3_cache: 32, // MB
        integrated_gpu: "AMD Radeon Graphics",
        tdp: 65, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 281.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 5 8400F",
        manufacturer: "AMD",
        series: "Ryzen 5",
        cores: 6,
        threads: 12,
        base_clock: 4.2, // GHz
        max_boost_clock: 4.7, // GHz
        l3_cache: 16, // MB
        integrated_gpu: false,
        tdp: 65, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 164.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 7 5800X",
        manufacturer: "AMD",
        series: "Ryzen 7",
        cores: 8,
        threads: 16,
        base_clock: 3.8, // GHz
        max_boost_clock: 4.7, // GHz
        l3_cache: 32, // MB
        integrated_gpu: "AMD Radeon Graphics",
        tdp: 105, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 157.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 7 5700G",
        manufacturer: "AMD",
        series: "Ryzen 7",
        cores: 8,
        threads: 16,
        base_clock: 3.8, // GHz
        max_boost_clock: 4.6, // GHz
        l3_cache: 16, // MB
        integrated_gpu: "AMD Radeon Graphics 2 000 MHz",
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 164.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 7 5700X",
        manufacturer: "AMD",
        series: "Ryzen 7",
        cores: 8,
        threads: 16,
        base_clock: 3.4, // GHz
        max_boost_clock: 4.6, // GHz
        l3_cache: 32, // MB
        integrated_gpu: false,
        tdp: 65, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 176.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 7 5700X3D",
        manufacturer: "AMD",
        series: "Ryzen 7",
        cores: 8,
        threads: 16,
        base_clock: 3, // GHz
        max_boost_clock: 4.1, // GHz
        l3_cache: 96, // MB
        integrated_gpu: false,
        tdp: 105, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 233.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 7 5800XT",
        manufacturer: "AMD",
        series: "Ryzen 7",
        cores: 8,
        threads: 16,
        base_clock: 3.8, // GHz
        max_boost_clock: 4.8, // GHz
        l3_cache: 32, // MB
        integrated_gpu: false,
        tdp: 105, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 280.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 7 7700",
        manufacturer: "AMD",
        series: "Ryzen 7",
        cores: 8,
        threads: 16,
        base_clock: 3.8, // GHz
        max_boost_clock: 5.3, // GHz
        l3_cache: 32, // MB
        integrated_gpu: "AMD Radeon Graphics",
        tdp: 65, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 318.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 7 7700X",
        manufacturer: "AMD",
        series: "Ryzen 7",
        cores: 8,
        threads: 16,
        base_clock: 4.5, // GHz
        max_boost_clock: 5.4, // GHz
        l3_cache: 32, // MB
        integrated_gpu: "AMD Radeon Graphics 2 200 MHz",
        tdp: 105, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 330.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 7 9700X",
        manufacturer: "AMD",
        series: "Ryzen 7",
        cores: 8,
        threads: 16,
        base_clock: 3.8, // GHz
        max_boost_clock: 5.5, // GHz
        l3_cache: 32, // MB
        integrated_gpu: "AMD Radeon Graphics",
        tdp: 65, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 374.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 7 7800X3D",
        manufacturer: "AMD",
        series: "Ryzen 7",
        cores: 8,
        threads: 16,
        base_clock: 4.2, // GHz
        max_boost_clock: 5, // GHz
        l3_cache: 96, // MB
        integrated_gpu: "AMD Radeon Graphics 2 200 MHz",
        tdp: 120, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 532.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 7 9800X3D",
        manufacturer: "AMD",
        series: "Ryzen 7",
        cores: 8,
        threads: 16,
        base_clock: 4.7, // GHz
        max_boost_clock: 5.2, // GHz
        l3_cache: 96, // MB
        integrated_gpu: "AMD Radeon Graphics",
        tdp: 120, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 607.72 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 9 5900X",
        manufacturer: "AMD",
        series: "Ryzen 9",
        cores: 12,
        threads: 24,
        base_clock: 3.7, // GHz
        max_boost_clock: 4.8, // GHz
        l3_cache: 64, // MB
        integrated_gpu: false,
        tdp: 105, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 233.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 9 5950X",
        manufacturer: "AMD",
        series: "Ryzen 9",
        cores: 16,
        threads: 32,
        base_clock: 3.4, // GHz
        max_boost_clock: 4.9, // GHz
        l3_cache: 64, // MB
        integrated_gpu: false,
        tdp: 105, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 330.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 9 7900",
        manufacturer: "AMD",
        series: "Ryzen 9",
        cores: 12,
        threads: 24,
        base_clock: 3.7, // GHz
        max_boost_clock: 5.4, // GHz
        l3_cache: 64, // MB
        integrated_gpu: "AMD Radeon Graphics",
        tdp: 65, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 378.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 9 7900X",
        manufacturer: "AMD",
        series: "Ryzen 9",
        cores: 12,
        threads: 24,
        base_clock: 4.7, // GHz
        max_boost_clock: 5.6, // GHz
        l3_cache: 64, // MB
        integrated_gpu: "AMD Radeon Graphics 2 200 MHz",
        tdp: 170, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 390.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 9 5900XT",
        manufacturer: "AMD",
        series: "Ryzen 9",
        cores: 16,
        threads: 32,
        base_clock: 3.3, // GHz
        max_boost_clock: 4.8, // GHz
        l3_cache: 64, // MB
        integrated_gpu: false,
        tdp: 105, // W
        socket: "AM4",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory: "", // GB
        price: 401.69 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 9 9900X",
        manufacturer: "AMD",
        series: "Ryzen 9",
        cores: 12,
        threads: 24,
        base_clock: 4.4, // GHz
        max_boost_clock: 5.6, // GHz
        l3_cache: 64, // MB
        integrated_gpu: "AMD Radeon Graphics",
        tdp: 120, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 483.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 9 7950X",
        manufacturer: "AMD",
        series: "Ryzen 9",
        cores: 16,
        threads: 32,
        base_clock: 4.5, // GHz
        max_boost_clock: 5.7, // GHz
        l3_cache: 64, // MB
        integrated_gpu: "AMD Radeon Graphics 2 200 MHz",
        tdp: 170, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 547.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 9 9950X",
        manufacturer: "AMD",
        series: "Ryzen 9",
        cores: 16,
        threads: 32,
        base_clock: 4.3, // GHz
        max_boost_clock: 5.7, // GHz
        l3_cache: 64, // MB
        integrated_gpu: "AMD Radeon Graphics",
        tdp: 170, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 704.9 // https://www.alza.sk/
    },

    {
        name: "AMD Ryzen 9 7950X3D",
        manufacturer: "AMD",
        series: "Ryzen 9",
        cores: 16,
        threads: 32,
        base_clock: 4.2, // GHz
        max_boost_clock: 5.7, // GHz
        l3_cache: 128, // MB
        integrated_gpu: "AMD Radeon Graphics 2 200 MHz",
        tdp: 120, // W
        socket: "AM5",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 716.9 // https://www.alza.sk/
    },

    // Intel

    {
        name: "Intel Core i3-12100F",
        manufacturer: "Intel",
        series: "Core i3",
        cores: 4,
        threads: 8,
        base_clock: 3.3, // GHz
        max_boost_clock: 4.3, // GHz
        l3_cache: 12, // MB
        integrated_gpu: false,
        tdp: 89, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 90.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i3-14100F",
        manufacturer: "Intel",
        series: "Core i3",
        cores: 4,
        threads: 8,
        base_clock: 3.5, // GHz
        max_boost_clock: 4.7, // GHz
        l3_cache: 12, // MB
        integrated_gpu: false,
        tdp: 110, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 93.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i3-12100",
        manufacturer: "Intel",
        series: "Core i3",
        cores: 4,
        threads: 8,
        base_clock: 3.3, // GHz
        max_boost_clock: 4.3, // GHz
        l3_cache: 12, // MB
        integrated_gpu: "Intel UHD Graphics 730 1 400 MHz",
        tdp: 89, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 129.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i3-14100",
        manufacturer: "Intel",
        series: "Core i3",
        cores: 4,
        threads: 8,
        base_clock: 3.8, // GHz
        max_boost_clock: 4.7, // GHz
        l3_cache: 12, // MB
        integrated_gpu: "Intel UHD Graphics 730",
        tdp: 110, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 131.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i5-12400F",
        manufacturer: "Intel",
        series: "Core i5",
        cores: 6,
        threads: 12,
        base_clock: 2.5, // GHz
        max_boost_clock: 4.4, // GHz
        l3_cache: 18, // MB
        integrated_gpu: false,
        tdp: 117, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 120.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i5-14400F",
        manufacturer: "Intel",
        series: "Core i5",
        cores: 10,
        threads: 16,
        base_clock: 2.7, // GHz
        max_boost_clock: 4.7, // GHz
        l3_cache: 20, // MB
        integrated_gpu: false,
        tdp: 148, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 153.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i5-13400F",
        manufacturer: "Intel",
        series: "Core i5",
        cores: 10,
        threads: 16,
        base_clock: 2.5, // GHz
        max_boost_clock: 4.6, // GHz
        l3_cache: 20, // MB
        integrated_gpu: false,
        tdp: 148, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 155.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i5-12400",
        manufacturer: "Intel",
        series: "Core i5",
        cores: 6,
        threads: 12,
        base_clock: 2.5, // GHz
        max_boost_clock: 4.4, // GHz
        l3_cache: 18, // MB
        integrated_gpu: "Intel UHD Graphics 730 1 450 MHz",
        tdp: 117, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 163.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i5-14400",
        manufacturer: "Intel",
        series: "Core i5",
        cores: 10,
        threads: 16,
        base_clock: 2.7, // GHz
        max_boost_clock: 4.7, // GHz
        l3_cache: 20, // MB
        integrated_gpu: "Intel UHD Graphics 730 1 550 MHz",
        tdp: 148, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 173.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i5-13400",
        manufacturer: "Intel",
        series: "Core i5",
        cores: 10,
        threads: 16,
        base_clock: 2.5, // GHz
        max_boost_clock: 4.6, // GHz
        l3_cache: 20, // MB
        integrated_gpu: "Intel UHD Graphics 730",
        tdp: 154, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 179.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i5-12600K",
        manufacturer: "Intel",
        series: "Core i5",
        cores: 10,
        threads: 16,
        base_clock: 3.7, // GHz
        max_boost_clock: 4.9, // GHz
        l3_cache: 20, // MB
        integrated_gpu: "Intel UHD Graphics 770",
        tdp: 150, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 191.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i5-14600KF",
        manufacturer: "Intel",
        series: "Core i5",
        cores: 14,
        threads: 20,
        base_clock: 3.5, // GHz
        max_boost_clock: 5.3, // GHz
        l3_cache: 24, // MB
        integrated_gpu: false,
        tdp: 181, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 230.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i5-14600K",
        manufacturer: "Intel",
        series: "Core i5",
        cores: 14,
        threads: 20,
        base_clock: 3.5, // GHz
        max_boost_clock: 5.3, // GHz
        l3_cache: 24, // MB
        integrated_gpu: "Intel UHD Graphics 770",
        tdp: 181, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 269.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i7-12700KF",
        manufacturer: "Intel",
        series: "Core i7",
        cores: 12,
        threads: 20,
        base_clock: 3.6, // GHz
        max_boost_clock: 5, // GHz
        l3_cache: 25, // MB
        integrated_gpu: false,
        tdp: 125, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 213.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i7-14700KF",
        manufacturer: "Intel",
        series: "Core i7",
        cores: 20,
        threads: 28,
        base_clock: 3.4, // GHz
        max_boost_clock: 5.6, // GHz
        l3_cache: 33, // MB
        integrated_gpu: false,
        tdp: 253, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 386.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i7-14700K",
        manufacturer: "Intel",
        series: "Core i7",
        cores: 20,
        threads: 28,
        base_clock: 3.4, // GHz
        max_boost_clock: 5.6, // GHz
        l3_cache: 33, // MB
        integrated_gpu: "Intel UHD Graphics 770",
        tdp: 253, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 421.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i9-14900KF",
        manufacturer: "Intel",
        series: "Core i9",
        cores: 24,
        threads: 32,
        base_clock: 3.2, // GHz
        max_boost_clock: 6, // GHz
        l3_cache: 36, // MB
        integrated_gpu: false,
        tdp: 253, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 501.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core i9-14900K",
        manufacturer: "Intel",
        series: "Core i9",
        cores: 24,
        threads: 32,
        base_clock: 3.2, // GHz
        max_boost_clock: 6, // GHz
        l3_cache: 36, // MB
        integrated_gpu: "Intel UHD Graphics 770",
        tdp: 253, // W
        socket: "1700",
        memory_type: "DDR4 / DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 522.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core Ultra 5 245KF",
        manufacturer: "Intel",
        series: "Core Ultra 5",
        cores: 14,
        threads: 14,
        base_clock: 5.2, // GHz
        max_boost_clock: 5.2, // GHz
        l3_cache: 24, // MB
        integrated_gpu: false,
        tdp: 159, // W
        socket: "1851",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 340.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core Ultra 5 245K",
        manufacturer: "Intel",
        series: "Core Ultra 5",
        cores: 14,
        threads: 14,
        base_clock: 5.2, // GHz
        max_boost_clock: 5.2, // GHz
        l3_cache: 24, // MB
        integrated_gpu: "Intel Graphics",
        tdp: 159, // W
        socket: "1851",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 360.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core Ultra 7 265KF",
        manufacturer: "Intel",
        series: "Core Ultra 7",
        cores: 20,
        threads: 20,
        base_clock: 5.5, // GHz
        max_boost_clock: 5.5, // GHz
        l3_cache: 30, // MB
        integrated_gpu: false,
        tdp: 250, // W
        socket: "1851",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 453.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core Ultra 7 265K",
        manufacturer: "Intel",
        series: "Core Ultra 7",
        cores: 20,
        threads: 20,
        base_clock: 5.5, // GHz
        max_boost_clock: 5.5, // GHz
        l3_cache: 30, // MB
        integrated_gpu: "Intel Graphics",
        tdp: 250, // W
        socket: "1851",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 468.9 // https://www.alza.sk/
    },

    {
        name: "Intel Core Ultra 9 285K",
        manufacturer: "Intel",
        series: "Core Ultra 9",
        cores: 24,
        threads: 24,
        base_clock: 3.2, // GHz
        max_boost_clock: 5.7, // GHz
        l3_cache: 36, // MB
        integrated_gpu: "Intel Graphics",
        tdp: 250, // W
        socket: "1851",
        memory_type: "DDR5",
        memory_channels: 2,
        max_memory: "", // GB
        price: 701.9 // https://www.alza.sk/
    }
]

document.querySelector(".processor_search_input").addEventListener("input", function(event){
    let processor_search_filter = processors.filter(function(one_processor){
        return one_processor.name.toLowerCase().includes(event.target.value.toLowerCase())
    })

    document.querySelector(".processor_searched_results").innerHTML = ""

    processor_search_filter.forEach(function(one_processor){
        let processor_searched_results_paragraph = document.createElement("p")
        processor_searched_results_paragraph.innerHTML = `<b>Názov procesora:</b> ${one_processor.name}`
        document.querySelector(".processor_searched_results").appendChild(processor_searched_results_paragraph)
    })

    if(event.target.value === ""){
        document.querySelector(".processor_searched_results").innerHTML = ""
    }
})

let motherboard = [
    {
        name: "ASROCK A520M-HVS",
        manufacturer: "ASROCK",
        socket: "AM4",
        chipset: "A520",
        form: "mATX",
        memory_type: "DDR4",
        memory_channels: 2,
        max_memory_frequency: 4733, // MHz
        m2_slots: 2,
    },

    {
        
    },
]