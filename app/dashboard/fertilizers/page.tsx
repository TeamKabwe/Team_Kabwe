"use client"

import { useState } from "react"

// Mock fertilizer data
const fertilizers = [
  {
    id: "1",
    name: "NPK 15-15-15",
    type: "Compound",
    price: 45.99,
    availability: "In Stock",
    description: "Balanced fertilizer suitable for most crops",
  },
  {
    id: "2",
    name: "Urea 46%",
    type: "Nitrogen",
    price: 38.5,
    availability: "In Stock",
    description: "High nitrogen content for leafy growth",
  },
  {
    id: "3",
    name: "DAP 18-46-0",
    type: "Phosphate",
    price: 52.75,
    availability: "Limited",
    description: "High phosphorus content for root development",
  },
  {
    id: "4",
    name: "Potash 0-0-60",
    type: "Potassium",
    price: 41.25,
    availability: "Out of Stock",
    description: "High potassium content for fruit and flower development",
  },
]

export default function FertilizersPage() {
  const [activeTab, setActiveTab] = useState("available")
  const [isApplying, setIsApplying] = useState(false)

  const handleApply = (id: string) => {
    setIsApplying(true)

    // Simulate API call
    setTimeout(() => {
      setIsApplying(false)
      alert(`Applied for subsidy on fertilizer ID: ${id}`)
    }, 1000)
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Fertilizer Access</h1>

      <div className="mb-6">
        <div className="border-b border-gray-200">
          <nav className="-mb-px flex">
            <button
              onClick={() => setActiveTab("available")}
              className={`py-2 px-4 text-center border-b-2 font-medium text-sm ${
                activeTab === "available"
                  ? "border-blue-500 text-blue-600"
                  : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
              }`}
            >
              Available Fertilizers
            </button>
            <button
              onClick={() => setActiveTab("apply")}
              className={`py-2 px-4 text-center border-b-2 font-medium text-sm ${
                activeTab === "apply"
                  ? "border-blue-500 text-blue-600"
                  : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
              }`}
            >
              Apply for Subsidy
            </button>
            <button
              onClick={() => setActiveTab("track")}
              className={`py-2 px-4 text-center border-b-2 font-medium text-sm ${
                activeTab === "track"
                  ? "border-blue-500 text-blue-600"
                  : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
              }`}
            >
              Track Delivery
            </button>
          </nav>
        </div>
      </div>

      {activeTab === "available" && (
        <div className="grid gap-4 md:grid-cols-2">
          {fertilizers.map((fertilizer) => (
            <div key={fertilizer.id} className="bg-white p-4 rounded-lg shadow">
              <div className="flex justify-between items-start mb-2">
                <div>
                  <h3 className="font-semibold">{fertilizer.name}</h3>
                  <p className="text-sm text-gray-500">{fertilizer.type}</p>
                </div>
                <span
                  className={`px-2 py-1 rounded text-xs ${
                    fertilizer.availability === "In Stock"
                      ? "bg-green-100 text-green-800"
                      : fertilizer.availability === "Limited"
                        ? "bg-yellow-100 text-yellow-800"
                        : "bg-red-100 text-red-800"
                  }`}
                >
                  {fertilizer.availability}
                </span>
              </div>
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-gray-500">Price:</span>
                  <span className="font-medium">${fertilizer.price.toFixed(2)}</span>
                </div>
                <p className="text-sm">{fertilizer.description}</p>
                <button
                  onClick={() => handleApply(fertilizer.id)}
                  disabled={fertilizer.availability === "Out of Stock" || isApplying}
                  className="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                >
                  {isApplying ? "Applying..." : "Apply for Subsidy"}
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {activeTab === "apply" && (
        <div className="bg-white p-6 rounded-lg shadow">
          <h2 className="text-xl font-semibold mb-4">Apply for Subsidy</h2>
          <form className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Fertilizer Type</label>
              <select className="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="">Select fertilizer type</option>
                <option value="npk">NPK 15-15-15</option>
                <option value="urea">Urea 46%</option>
                <option value="dap">DAP 18-46-0</option>
                <option value="potash">Potash 0-0-60</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
              <div className="flex gap-2">
                <input
                  type="number"
                  min="1"
                  placeholder="1"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md"
                />
                <select className="w-[120px] px-3 py-2 border border-gray-300 rounded-md">
                  <option value="bags">Bags (50kg)</option>
                  <option value="tons">Tons</option>
                </select>
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Crop Type</label>
              <select className="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="">Select crop type</option>
                <option value="maize">Maize</option>
                <option value="wheat">Wheat</option>
                <option value="rice">Rice</option>
                <option value="soybeans">Soybeans</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
              <input
                type="text"
                placeholder="Enter your delivery address"
                className="w-full px-3 py-2 border border-gray-300 rounded-md"
              />
            </div>

            <button type="submit" className="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700">
              Submit Application
            </button>
          </form>
        </div>
      )}

      {activeTab === "track" && (
        <div className="bg-white p-6 rounded-lg shadow">
          <h2 className="text-xl font-semibold mb-4">Track Delivery</h2>

          <div className="border rounded-lg p-4">
            <div className="flex justify-between items-start mb-2">
              <div>
                <h3 className="font-medium">NPK 15-15-15</h3>
                <p className="text-sm text-gray-500">2 bags (50kg each)</p>
              </div>
              <span className="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">In Transit</span>
            </div>
            <div className="space-y-1 text-sm">
              <div className="flex justify-between">
                <span className="text-gray-500">Tracking ID:</span>
                <span>DEL-12345</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-500">Estimated Delivery:</span>
                <span>2023-11-10</span>
              </div>
            </div>
            <div className="mt-4">
              <div className="relative pt-1">
                <div className="flex mb-2 items-center justify-between">
                  <div className="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full bg-green-100 text-green-800">
                    Processing
                  </div>
                  <div className="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full bg-blue-100 text-blue-800">
                    In Transit
                  </div>
                  <div className="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full bg-gray-100 text-gray-800">
                    Delivered
                  </div>
                </div>
                <div className="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                  <div
                    style={{ width: "66%" }}
                    className="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

