import { describe, it, expect, vi } from "vitest";
import { screen, waitFor } from "@testing-library/react";
import { renderWithQueryClient } from "@/test/render-with-providers";
import MonitoringPage from "./page";

vi.mock("@/lib/api/monitoring.service", () => ({
  fetchMonitoringList: vi.fn().mockResolvedValue({ data: [], total: 0, lastPage: 1 }),
}));

describe("MonitoringPage", () => {
  it("renders without throwing and shows the empty state", async () => {
    renderWithQueryClient(<MonitoringPage />);

    expect(screen.getByText("Monitoring")).toBeTruthy();

    await waitFor(() => {
      expect(screen.getByText("0 activity log entries")).toBeTruthy();
    });
  });
});
