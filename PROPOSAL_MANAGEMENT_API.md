# Proposal Management API Documentation

## Overview
This API provides comprehensive endpoints for managing land lease proposals in the FARMMASTER system. It supports creating, updating, viewing, and managing proposal statuses for landowners and farm managers.

## Base URL
```
/api/proposals
```

## Authentication
All endpoints require authentication. Some endpoints require specific roles:
- **Landowner**: Can view their own proposals
- **Financial_Manager**: Can view and manage all proposals
- **Operational_Manager**: Can view and manage all proposals

## API Endpoints

### 1. Get All Proposals
**GET** `/api/proposals`

Returns all proposals (for managers) or user-specific proposals.

**Query Parameters:**
- `user_id` (optional) - Filter by specific user ID
- `status` (optional) - Filter by status (Pending, Accepted, Rejected)
- `crop_type` (optional) - Filter by crop type
- `date_from` (optional) - Filter from date (YYYY-MM-DD)
- `date_to` (optional) - Filter to date (YYYY-MM-DD)
- `min_rental` (optional) - Minimum rental value filter
- `max_rental` (optional) - Maximum rental value filter

**Response:**
```json
{
  "status": "success",
  "message": "All proposals retrieved successfully",
  "data": [
    {
      "proposal_id": 1,
      "proposal_display_id": "#2025001",
      "user_id": 32,
      "landowner_name": "Nuwani Silva",
      "email": "lo@gmail.com",
      "land_id": 19,
      "location": "Galle",
      "size": 12.000,
      "crop_type": "Organic Vegetables (Tomato, Carrot)",
      "estimated_yield": 10000.00,
      "lease_duration_years": 3,
      "rental_value": 50000.00,
      "profit_sharing_farmmaster": 60.00,
      "profit_sharing_landowner": 40.00,
      "estimated_profit_landowner": 80000.00,
      "status": "Accepted",
      "proposal_date": "2025-08-15",
      "created_at": "2025-08-28T06:53:15.000Z",
      "updated_at": "2025-09-12T18:35:42.000Z"
    }
  ]
}
```

### 2. Get Specific Proposal
**GET** `/api/proposals/{proposal_id}`

Returns detailed information about a specific proposal.

**Response:**
```json
{
  "status": "success",
  "message": "Proposal retrieved successfully",
  "data": {
    "proposal_id": 1,
    "user_id": 32,
    "user_name": "Nuwani Silva",
    "user_email": "lo@gmail.com",
    "land_id": 19,
    "location": "Galle",
    "land_size": 12.000,
    "crop_type": "Organic Vegetables (Tomato, Carrot)",
    "estimated_yield": 10000.00,
    "lease_duration_years": 3,
    "rental_value": 50000.00,
    "profit_sharing_farmmaster": 60.00,
    "profit_sharing_landowner": 40.00,
    "estimated_profit_landowner": 80000.00,
    "status": "Accepted",
    "proposal_date": "2025-08-15"
  }
}
```

### 3. Create New Proposal
**POST** `/api/proposals`

Creates a new land lease proposal.

**Request Body:**
```json
{
  "land_id": 19,
  "user_id": 32,
  "crop_type": "Organic Vegetables",
  "estimated_yield": 10000.00,
  "lease_duration_years": 3,
  "rental_value": 50000.00,
  "profit_sharing_farmmaster": 60.00,
  "profit_sharing_landowner": 40.00,
  "estimated_profit_landowner": 80000.00,
  "proposal_date": "2025-08-15"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Proposal created successfully",
  "proposal_id": 7
}
```

### 4. Update Proposal Status
**PUT** `/api/proposals/{proposal_id}/status`

Updates the status of a proposal (Accept/Reject/Pending).

**Request Body:**
```json
{
  "status": "Accepted",
  "notes": "Proposal looks good for organic farming"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Proposal status updated successfully"
}
```

### 5. Update Proposal Details
**PUT** `/api/proposals/{proposal_id}`

Updates proposal information (crop type, yield, rental value, etc.).

**Request Body:**
```json
{
  "crop_type": "Mixed Vegetables",
  "estimated_yield": 12000.00,
  "rental_value": 55000.00,
  "estimated_profit_landowner": 85000.00
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Proposal updated successfully"
}
```

### 6. Delete Proposal
**DELETE** `/api/proposals/{proposal_id}`

Deletes a specific proposal.

**Response:**
```json
{
  "status": "success",
  "message": "Proposal deleted successfully"
}
```

### 7. Get Proposal Statistics
**GET** `/api/proposals/stats`

Returns overall proposal statistics.

**Response:**
```json
{
  "status": "success",
  "message": "Proposal statistics retrieved successfully",
  "data": {
    "total_proposals": 6,
    "pending_proposals": 3,
    "accepted_proposals": 2,
    "rejected_proposals": 1,
    "average_rental_value": 52500.00,
    "total_estimated_profit": 420000.00
  }
}
```

### 8. Search Proposals
**GET** `/api/proposals/search?q={search_term}`

Searches proposals by landowner name, crop type, or location.

**Query Parameters:**
- `q` (required) - Search term
- `user_id` (optional) - Limit search to specific user

**Response:**
```json
{
  "status": "success",
  "message": "Search results retrieved successfully",
  "data": [
    {
      "proposal_id": 1,
      "user_name": "Nuwani Silva",
      "crop_type": "Organic Vegetables",
      "location": "Galle",
      "status": "Accepted",
      "estimated_profit_landowner": 80000.00
    }
  ]
}
```

### 9. Get Proposals by Status
**GET** `/api/proposals/status/{status}`

Returns all proposals with a specific status.

**Valid Status Values:**
- `Pending`
- `Accepted` 
- `Rejected`

**Response:**
```json
{
  "status": "success",
  "message": "Proposals by status retrieved successfully",
  "data": [
    {
      "proposal_id": 3,
      "landowner_name": "Nuwani Silva",
      "crop_type": "Premium Organic Crops",
      "status": "Pending",
      "estimated_profit_landowner": 100000.00
    }
  ]
}
```

### 10. Get Land-Specific Proposals
**GET** `/api/proposals/land/{land_id}`

Returns all proposals for a specific land.

**Response:**
```json
{
  "status": "success",
  "message": "Land proposals retrieved successfully",
  "data": [
    {
      "proposal_id": 1,
      "crop_type": "Organic Vegetables",
      "status": "Accepted",
      "rental_value": 50000.00,
      "estimated_profit_landowner": 80000.00
    }
  ]
}
```

## Frontend Integration

### React Component Integration

For your `ProposalManagement.jsx` component, here's how to integrate with the API:

```javascript
import React, { useState, useEffect } from 'react';

const ProposalManagement = () => {
  const [proposals, setProposals] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Fetch all proposals
  useEffect(() => {
    fetchProposals();
  }, []);

  const fetchProposals = async () => {
    try {
      setLoading(true);
      const response = await fetch('/api/proposals', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      });
      
      const data = await response.json();
      
      if (data.status === 'success') {
        setProposals(data.data);
      } else {
        setError(data.message);
      }
    } catch (err) {
      setError('Failed to fetch proposals');
    } finally {
      setLoading(false);
    }
  };

  const updateProposalStatus = async (proposalId, status) => {
    try {
      const response = await fetch(`/api/proposals/${proposalId}/status`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status })
      });
      
      const data = await response.json();
      
      if (data.status === 'success') {
        // Refresh proposals list
        fetchProposals();
      } else {
        setError(data.message);
      }
    } catch (err) {
      setError('Failed to update proposal status');
    }
  };

  // Transform API data to match your frontend format
  const transformedProposals = proposals.map(proposal => ({
    id: proposal.proposal_display_id || `#${proposal.proposal_id}`,
    name: proposal.landowner_name,
    status: proposal.status,
    profit: `Rs. ${proposal.estimated_profit_landowner.toLocaleString()}`
  }));

  return (
    <div className="flex-1 bg-white min-h-screen p-4 md:p-10 font-poppins">
      <div className="mb-6">
        <h1 className="text-3xl md:text-4xl font-bold text-black mb-4 mt-4">
          Proposal Management
        </h1>
        <p className="text-green-600 mt-1">
          Manage and send proposals to landowners.
        </p>
      </div>

      {loading && <p>Loading proposals...</p>}
      {error && <p className="text-red-500">{error}</p>}

      <div className="overflow-x-auto bg-white rounded-lg shadow-sm">
        <table className="min-w-full border border-gray-200">
          <thead>
            <tr className="bg-green-50 text-left text-sm text-gray-700">
              <th className="py-3 px-4">Proposal ID</th>
              <th className="py-3 px-4">Landowner Name</th>
              <th className="py-3 px-4">Status</th>
              <th className="py-3 px-4">Profit Estimate</th>
              <th className="py-3 px-4 text-green-600">Actions</th>
            </tr>
          </thead>
          <tbody className="text-sm">
            {transformedProposals.map((proposal, index) => (
              <tr key={index} className="border-t border-gray-200 hover:bg-green-50 transition">
                <td className="py-3 px-4">{proposal.id}</td>
                <td className="py-3 px-4 text-green-700">{proposal.name}</td>
                <td className="py-3 px-4">
                  <span className={`inline-block px-3 py-1 rounded-full text-sm ${getStatusStyles(proposal.status)}`}>
                    {proposal.status}
                  </span>
                </td>
                <td className="py-3 px-4 text-green-700">{proposal.profit}</td>
                <td className="py-3 px-4">
                  <button
                    onClick={() => handleViewDetails(proposal)}
                    className="text-black font-semibold hover:underline hover:text-green-600 cursor-pointer"
                  >
                    View Details
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

const getStatusStyles = (status) => {
  const styles = {
    'Pending': 'bg-green-50 text-black',
    'Accepted': 'bg-green-200 text-black font-semibold',
    'Rejected': 'bg-red-100 text-red-700 font-semibold'
  };
  return styles[status] || 'bg-gray-100 text-gray-700';
};

export default ProposalManagement;
```

## Error Handling

The API returns consistent error responses:

```json
{
  "status": "error",
  "message": "Error description here",
  "code": 400
}
```

Common HTTP status codes:
- `200` - Success
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (authentication required)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `500` - Internal Server Error

## Rate Limiting

Currently no rate limiting is implemented, but it's recommended to implement client-side throttling for better user experience.

## Notes

1. All monetary values are in the database's default currency (Sri Lankan Rupees)
2. Dates are returned in ISO 8601 format
3. The `proposal_display_id` field provides a user-friendly ID format (#2025001)
4. Profit sharing percentages should always total 100%
5. Role-based access control is enforced on all endpoints