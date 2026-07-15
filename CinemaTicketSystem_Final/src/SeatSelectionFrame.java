import javax.swing.*;
import java.awt.*;
import java.util.ArrayList;

public class SeatSelectionFrame extends JFrame {

    private static final long serialVersionUID = 1L;

    public SeatSelectionFrame(SeatManager seatManager, String movieTitle, String username) {

        setTitle("Seats for: " + movieTitle);
        setSize(900, 600);
        setLayout(new BorderLayout());

        ArrayList<String[]> sessionBookings = new ArrayList<>();

        JPanel legend = new JPanel(new FlowLayout(FlowLayout.LEFT));
        legend.add(makeColorBox(Color.GREEN)); legend.add(new JLabel("Available  "));
        legend.add(makeColorBox(Color.RED));   legend.add(new JLabel("Booked  "));
        legend.add(makeColorBox(Color.ORANGE));legend.add(new JLabel("VIP  "));

        JPanel seatPanel = new JPanel(new GridLayout(seatManager.getRows(), seatManager.getCols(), 5, 5));

        for (int row = 0; row < seatManager.getRows(); row++) {
            for (int col = 0; col < seatManager.getCols(); col++) {
                String  seatName  = "R" + (row + 1) + "C" + (col + 1);
                boolean isVip     = row < 2;
                JButton seatBtn   = new JButton(seatName);

                if (seatManager.isBooked(row, col)) {
                    seatBtn.setBackground(Color.RED);
                    seatBtn.setEnabled(false);
                } else if (isVip) {
                    seatBtn.setBackground(Color.ORANGE);
                } else {
                    seatBtn.setBackground(Color.GREEN);
                }

                int r = row, c = col;
                seatBtn.addActionListener(e -> {
                    if (seatManager.isBooked(r, c)) return;

                    String ticketType;
                    if (isVip) {
                        ticketType = "VIP";
                        JOptionPane.showMessageDialog(this, "This is a VIP seat. Price: RM35.00");
                    } else {
                        String[] options = {"Adult", "Student"};
                        int choice = JOptionPane.showOptionDialog(this,
                            "Select ticket type for " + seatName, "Ticket Type",
                            JOptionPane.DEFAULT_OPTION, JOptionPane.QUESTION_MESSAGE,
                            null, options, options[0]);
                        if (choice == -1) return;
                        ticketType = options[choice];
                    }

                    double  price   = Booking.calculatePrice(ticketType);
                    boolean success = seatManager.bookSeat(r, c, ticketType, price);

                    if (success) {
                        sessionBookings.add(new String[]{seatName, ticketType, String.format("%.2f", price)});
                        seatBtn.setBackground(Color.RED);
                        seatBtn.setEnabled(false);
                        JOptionPane.showMessageDialog(this, "Seat booked!\nSeat: " + seatName
                            + "\nType: " + ticketType + "\nPrice: RM" + String.format("%.2f", price));
                    } else {
                        JOptionPane.showMessageDialog(this, "Seat already booked!", "Error", JOptionPane.ERROR_MESSAGE);
                    }
                });
                seatPanel.add(seatBtn);
            }
        }

        JButton finishBtn = new JButton("Finish Booking & Add Snacks");
        finishBtn.addActionListener(e -> {
            if (sessionBookings.isEmpty()) {
                JOptionPane.showMessageDialog(this, "You haven't booked any seats yet!");
                return;
            }
            double ticketTotal = sessionBookings.stream()
                .mapToDouble(s -> Double.parseDouble(s[2])).sum();

            int addSnacks = JOptionPane.showConfirmDialog(this,
                "You booked " + sessionBookings.size() + " seat(s).\nTicket Total: RM"
                + String.format("%.2f", ticketTotal) + "\n\nWould you like to add concession items?",
                "Add Snacks?", JOptionPane.YES_NO_OPTION);

            if (addSnacks == JOptionPane.YES_OPTION) {
                dispose();
                new ConcessionFrame(movieTitle, ticketTotal, sessionBookings, username).setVisible(true);
            } else {
                String[] methods = {"Credit/Debit Card", "Touch 'n Go eWallet", "Online Banking", "Cash"};
                String payment = (String) JOptionPane.showInputDialog(
                    this, "Select Payment Method:", "Payment",
                    JOptionPane.QUESTION_MESSAGE, null, methods, methods[0]);
                if (payment == null) return;
                dispose();
                ReceiptFrame.show(movieTitle, ticketTotal, sessionBookings, new ArrayList<>(), payment, username);
            }
        });

        JPanel top = new JPanel(new BorderLayout());
        top.add(new JLabel("  🎬 SCREEN", SwingConstants.CENTER), BorderLayout.NORTH);
        top.add(legend, BorderLayout.SOUTH);

        add(top, BorderLayout.NORTH);
        add(seatPanel, BorderLayout.CENTER);
        add(finishBtn, BorderLayout.SOUTH);
        setLocationRelativeTo(null);
    }

    private JPanel makeColorBox(Color color) {
        JPanel box = new JPanel();
        box.setBackground(color);
        box.setPreferredSize(new Dimension(15, 15));
        return box;
    }
}
