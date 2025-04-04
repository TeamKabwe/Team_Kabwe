"use client"

import { useState } from "react"

// Mock market data
const marketData = [
  {
    id: "1",
    crop: "Maize",
    price: 320,
    unit: "per ton",
    region: "Central",
    date: "2023-11-01",
    trend: "up",
    change: 5.2,
  },
  {
    id: "2",
    crop: "Soybeans",
    price: 520,
    unit: "per ton",
    region: "Eastern",
    date: "2023-11-01",
    trend: "up",
    change: 8.7,
  },
  {
    id: "3",
    crop: "Wheat",
    price: 410,
    unit: "per ton",
    region: "Southern",
    date: "2023-11-01",
    trend: "down",
    change: 2.3,
  },
  {
    id: "4",
    crop: "Rice",
    price: 580,
    unit: "per ton",
    region: "Northern",
    date: "2023-11-01",
    trend: "stable",
    change: 0,
  },
  {
    id: "5",
    crop: "Groundnuts",
    price: 720,
    unit: "per ton",
    region: "Western",
    date: "2023-11-01",
    trend: "up",
    change: 12.5,
  },
  {
    id: "6",
    crop: "Cotton",
    price: 890,
    unit: "per ton",
    region: "Central",
    date: "2023-11-01",
    trend: "down",
    change: 3.8,
  },
]

// Available regions
const regions = ["All", "Central", "Eastern", "Northern", "Southern", "Western"]

// Available crops
const crops = ["All", "Maize", "Soybeans", "Wheat", "Rice", "Groundnuts", "Cotton"]

export default function MarketsPage() {
  const [filteredData, setFilteredData] = useState(marketData)
  const [selectedRegion, setSelectedRegion] = useState("All")
  const [selectedCrop, setSelectedCrop] = useState("All")

  const handleFilter = () => {
    let filtered = [...marketData]

    if (selectedRegion !== "All") {
      filtered = filtered.filter((item) => item.region === selectedRegion)
    }

    if (selectedCrop !== "All") {
      filtered = filtered.filter((item) => item.crop === selectedCrop)
    }

    setFilteredData(filtered)
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Market Prices</h1>

      <div className="bg-white p-6 rounded-lg shadow mb-6">
        <h2 className="text-lg font-semibold mb-4">Filter</h2>
        <div className="grid gap-4 sm:grid-cols-3">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Crop</label>
            <select
              value={selectedCrop}
              onChange={(e) => setSelectedCrop(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md"
            >
              {crops.map((crop) => (
                <option key={crop} value={crop}>
                  {crop}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Region</label>
            <select
              value={selectedRegion}
              onChange={(e) => setSelectedRegion(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md"
            >
              {regions.map((region) => (
                <option key={region} value={region}>
                  {region}
                </option>
              ))}
            </select>
          </div>

          <div className="flex items-end">
            <button
              onClick={handleFilter}
              className="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
              Apply Filters
            </button>
          </div>
        </div>
      </div>

      <div className="bg-white p-6 rounded-lg shadow">
        <h2 className="text-lg font-semibold mb-4">Latest Crop Prices</h2>

        {filteredData.length > 0 ? (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Crop
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Price
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Region
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Trend
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {filteredData.map((item) => (
                  <tr key={item.id}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{item.crop}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <div className="flex items-center">
                        ${item.price} {item.unit}
                        <span
                          className={`ml-2 text-xs ${
                            item.trend === "up"
                              ? "text-green-500"
                              : item.trend === "down"
                                ? "text-red-500"
                                : "text-gray-500"
                          }`}
                        >
                          {item.change > 0 && "+"}
                          {item.change !== 0 ? `${item.change}%` : ""}
                        </span>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{item.region}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {item.trend === "up" ? "Rising" : item.trend === "down" ? "Falling" : "Stable"}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{item.date}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="text-center py-8">
            <p className="text-gray-500">No market data available</p>
          </div>
        )}
      </div>
    </div>
  )
}

