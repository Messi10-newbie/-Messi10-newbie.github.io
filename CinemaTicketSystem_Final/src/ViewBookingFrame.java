import javax.swing.*;
import java.awt.*;
import java.io.*;
import java.util.ArrayList;

public class ViewBookingFrame extends JFrame {

    private static final long   serialVersionUID = 1L;
    private static final String BOOKING_FILE     = "bookings.txt";
    private SeatManager seatManager;
    private DefaultListModel<Booking> bookingModel = new DefaultListModel<>();
    private JList<Booking> bookingList = new JList<>(bookingModel);
    private JLabel totalLabel = new JLabel("Total Revenue: RM0.00");

    public ViewBookingFrame(SeatManager seatManager) {
        this.seatManager = seatManager;
        setTitle("View All Bookings");
        setSize(900, 500);
        setLayout(new BorderLayout(10, 10));

        loadBookings();

        bookingList.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);

        JButton deleteBtn    = new JButton("Cancel Selected Booking");
        JButton deleteAllBtn = new JButton("Cancel All Bookings");
        JButton refreshBtn   = new JButton("Refresh");
        JButton exitBtn      = new JButton("Close");

        JPanel bottom = new JPanel(new FlowLayout());
        bottom.add(deleteBtn);
        bottom.add(deleteAllBtn);
        bottom.add(refreshBtn);
        bottom.add(exitBtn);

        JPanel topPanel = new JPanel(new BorderLayout());
        topPanel.add(new JLabel("  All Bookings:"), BorderLayout.WEST);
        topPanel.add(totalLabel, BorderLayout.EAST);

        add(topPanel, BorderLayout.NORTH);
        add(new JScrollPane(bookingList), BorderLayout.CENTER);
        add(bottom, BorderLayout.SOUTH);

        deleteBtn.addActionListener(e -> deleteSelected());
        deleteAllBtn.addActionListener(e -> deleteAll());
        refreshBtn.addActionListener(e -> loadBookings());
        exitBtn.addActionListener(e -> dispose());

        setLocationRelativeTo(null);
    }

    private void loadBookings() {
        bookingModel.clear();
        File file = new File(BOOKING_FILE);
        if (!file.exists()) {
            updateTotalLabel();
            return;
        }

        try (BufferedReader br = new BufferedReader(new FileReader(file))) {
            String line;
            while ((line = br.readLine()) != null) {
                String[] parts = line.split(",");
                if (parts.length == 4) {
                    double price = Double.parseDouble(parts[3]);
                    bookingModel.addElement(new Booking(parts[0], parts[1], parts[2], price));
                }
            }
        } catch (IOException e) {
            System.err.println("Error loading bookings: " + e.getMessage());
            JOptionPane.showMessageDialog(this, "Error loading bookings.", "Error", JOptionPane.ERROR_MESSAGE);
        } catch (NumberFormatException e) {
            System.err.println("Error parsing booking data: " + e.getMessage());
        }
        updateTotalLabel();
    }

    private void deleteSelected() {
        Booking b = bookingList.getSelectedValue();
        if (b == null) {
            JOptionPane.showMessageDialog(this, "Please select a booking to cancel.");
            return;
        }

        int confirm = JOptionPane.showConfirmDialog(this,
            "Cancel booking: " + b.getSeat() + " for " + b.getMovie() + "?",
            "Confirm Cancel", JOptionPane.YES_NO_OPTION);

        if (confirm == JOptionPane.YES_OPTION) {
            seatManager.resetSeat(b.getMovie(), b.getSeat());
            bookingModel.removeElement(b);
            saveAll();
            loadBookings();
            JOptionPane.showMessageDialog(this, "Booking for " + b.getSeat() + " has been cancelled.");
        }
    }

    private void deleteAll() {
        if (bookingModel.isEmpty()) {
            JOptionPane.showMessageDialog(this, "No bookings to cancel.");
            return;
        }

        int confirm = JOptionPane.showConfirmDialog(this,
            "Cancel ALL bookings?", "Confirm", JOptionPane.YES_NO_OPTION);

        if (confirm == JOptionPane.YES_OPTION) {
            for (int i = 0; i < bookingModel.size(); i++) {
                Booking b = bookingModel.get(i);
                seatManager.resetSeat(b.getMovie(), b.getSeat());
            }
            bookingModel.clear();
            saveAll();
            loadBookings();
            JOptionPane.showMessageDialog(this, "All bookings have been cancelled.");
        }
    }

    private void saveAll() {
        try (PrintWriter pw = new PrintWriter(new FileWriter(BOOKING_FILE))) {
            for (int i = 0; i < bookingModel.size(); i++) {
                Booking b = bookingModel.get(i);
                pw.println(b.getMovie() + "," + b.getSeat() + "," + b.getType() + "," + b.getPrice());
            }
        } catch (IOException e) {
            System.err.println("Error saving bookings: " + e.getMessage());
            JOptionPane.showMessageDialog(this, "Error saving bookings.", "Error", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void updateTotalLabel() {
        double total = 0.0;
        for (int i = 0; i < bookingModel.size(); i++) {
            total += bookingModel.get(i).getPrice();
        }
        totalLabel.setText("Total Revenue: RM" + String.format("%.2f", total) + "  ");
    }
}
