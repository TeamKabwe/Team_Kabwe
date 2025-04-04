"use client"

import { useAuth } from "@/lib/auth-context"

export default function DashboardPage() {
  const { user } = useAuth()

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Welcome, {user?.name}</h1>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div className="bg-white p-4 rounded-lg shadow">
          <h2 className="font-semibold mb-2">Digital ID</h2>
          <div className="bg-green-100 text-green-800 px-2 py-1 rounded text-sm inline-block">Verified</div>
          <p className="text-sm text-gray-500 mt-2">Verified on 2023-10-15</p>
        </div>

        <div className="bg-white p-4 rounded-lg shadow">
          <h2 className="font-semibold mb-2">Loan Status</h2>
          <div className="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm inline-block">Pending</div>
          <p className="text-sm text-gray-500 mt-2">Application #12345</p>
        </div>

        <div className="bg-white p-4 rounded-lg shadow">
          <h2 className="font-semibold mb-2">Market Alerts</h2>
          <p className="text-xl font-bold">3</p>
          <p className="text-sm text-gray-500 mt-2">+2 since last week</p>
        </div>

        <div className="bg-white p-4 rounded-lg shadow">
          <h2 className="font-semibold mb-2">Fertilizer Voucher</h2>
          <p className="text-xl font-bold">Available</p>
          <p className="text-sm text-gray-500 mt-2">Expires in 30 days</p>
        </div>
      </div>

      <div className="bg-white p-6 rounded-lg shadow mb-6">
        <h2 className="text-xl font-semibold mb-4">Recent Activity</h2>
        <div className="space-y-4">
          <div className="flex justify-between pb-2 border-b">
            <div>
              <p className="font-medium">Loan Application Submitted</p>
              <p className="text-sm text-gray-500">For maize production</p>
            </div>
            <p className="text-sm text-gray-500">2 days ago</p>
          </div>
          <div className="flex justify-between pb-2 border-b">
            <div>
              <p className="font-medium">Fertilizer Voucher Claimed</p>
              <p className="text-sm text-gray-500">50kg NPK fertilizer</p>
            </div>
            <p className="text-sm text-gray-500">1 week ago</p>
          </div>
          <div className="flex justify-between">
            <div>
              <p className="font-medium">Digital ID Verified</p>
              <p className="text-sm text-gray-500">Via MOSIP</p>
            </div>
            <p className="text-sm text-gray-500">2 weeks ago</p>
          </div>
        </div>
      </div>
    </div>
  )
}

