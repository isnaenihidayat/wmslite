import { describe, it, expect, vi } from "vitest";
import { screen, waitFor } from "@testing-library/react";
import { renderWithQueryClient } from "@/test/render-with-providers";
import ShipmentPage from "./page";

vi.mock("next-auth/react", () => ({
  useSession: () => ({
    data: { user: { admin: 1, type: 2, module: 4 } },
    status: "authenticated",
  }),
}));

vi.mock("@/lib/api/shipment.service", () => ({
  fetchShipmentList: vi.fn().mockResolvedValue({ data: [], total: 0, lastPage: 1 }),
}));

describe("ShipmentPage", () => {
  it("renders without throwing and shows the Add button", async () => {
    renderWithQueryClient(<ShipmentPage />);

    expect(screen.getByText("Shipment")).toBeTruthy();

    await waitFor(() => {
      expect(screen.getByText("0 total records")).toBeTruthy();
    });

    // Scope: this smoke test only confirms the primary "Add Shipment" button
    // renders. The full push-to-inbound dialog interaction (open dialog, fill
    // form, submit, verify from_shipment flips) is out of scope per the plan's
    // Step C3 -- see backlog artifact
    // shipment-push-inbound-interaction-test_NOTE_19-06-26.md (deferred until
    // Phase 2 confirms shipment-status-flow semantics).
    expect(screen.getByRole("button", { name: /add shipment/i })).toBeTruthy();
  });
});
