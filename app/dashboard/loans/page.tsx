"use client"

import type React from "react"

import { useState } from "react"

export default function LoansPage() {
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [applications, setApplications] = useState([
    {
      id: "1",
      amount: "5,000",
      purpose: "Maize production",
      status: "Pending",
      date: "2023-10-15",
    },
    {
      id: "2",
      amount: "2,500",
      purpose: "Farm equipment",
      status: "Approved",
      date: "2023-08-22",
    },
  ])

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    setIsSubmitting(true)

    // Simulate API call
    setTimeout(() => {
      const formData = new FormData(e.target as HTMLFormElement)
      const newApplication = {
        id: (applications.length + 1).toString(),
        amount: formData.get("amount") as string,
        purpose: formData.get("purpose") as string,
        status: "Pending",
        date: new Date().toISOString().split("T")[0],
      }

      setApplications([newApplication, ...applications])
      setIsSubmitting(false)(
        // Reset form
        e.target as HTMLFormElement,
      ).reset()
    }, 1000)
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Apply for Loan</h1>

      <div className="grid md:grid-cols-2 gap-6">
        <div className="bg-white p-6 rounded-lg shadow">
          <h2 className="text-xl font-semibold mb-4">Apply Now</h2>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Loan Amount</label>
              <div className="relative">
                <span className="absolute left-3 top-1/2 -translate-y-1/2">$</span>
                <input
                  name="amount"
                  type="text"
                  placeholder="1,000"
                  className="pl-7 w-full px-3 py-2 border border-gray-300 rounded-md"
                  required
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
              <select name="purpose" className="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                <option value="">Select purpose</option>
                <option value="Crop Production">Crop Production</option>
                <option value="Livestock">Livestock</option>
                <option value="Farm Equipment">Farm Equipment</option>
                <option value="Farm Infrastructure">Farm Infrastructure</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Farm Size</label>
              <div className="flex gap-2">
                <input
                  name="farm_size"
                  type="text"
                  placeholder="5"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md"
                  required
                />
                <select name="size_unit" className="w-[120px] px-3 py-2 border border-gray-300 rounded-md">
                  <option value="hectares">Hectares</option>
                  <option value="acres">Acres</option>
                </select>
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Additional Details</label>
              <textarea
                name="details"
                placeholder="Provide any additional information about your loan request"
                rows={3}
                className="w-full px-3 py-2 border border-gray-300 rounded-md"
              ></textarea>
            </div>

            <button
              type="submit"
              disabled={isSubmitting}
              className="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              {isSubmitting ? "Submitting..." : "Submit Application"}
            </button>
          </form>
        </div>

        <div className="bg-white p-6 rounded-lg shadow">
          <h2 className="text-xl font-semibold mb-4">Your Applications</h2>

          {applications.length > 0 ? (
            <div className="space-y-4">
              {applications.map((app) => (
                <div key={app.id} className="flex items-center justify-between border-b pb-4 last:border-0 last:pb-0">
                  <div>
                    <p className="font-medium">${app.amount}</p>
                    <p className="text-sm text-gray-500">{app.purpose}</p>
                    <p className="text-xs text-gray-500">{app.date}</p>
                  </div>
                  <div>
                    <span
                      className={`px-2 py-1 rounded text-sm ${
                        app.status === "Approved"
                          ? "bg-green-100 text-green-800"
                          : app.status === "Rejected"
                            ? "bg-red-100 text-red-800"
                            : "bg-yellow-100 text-yellow-800"
                      }`}
                    >
                      {app.status}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-center text-gray-500 py-8">No loan applications yet</p>
          )}
        </div>
      </div>
    </div>
  )
}

